<?php

namespace HimmelKreis4865\AntiXray;

use HimmelKreis4865\AntiXray\tasks\BlockCalculationTask;
use HimmelKreis4865\AntiXray\tasks\ChunkModificationTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Server;
use function array_map;

class EventListener implements Listener {
	/**
	 * @param DataPacketSendEvent $event
	 *
	 * @ignoreCancelled false
	 */
	public function onDataSend(DataPacketSendEvent $event) {
		/** @var $batch BatchPacket */
		if (($batch = $event->getPacket()) instanceof BatchPacket && !($batch instanceof ModifiedChunk)) {
			$batch->decode();
			
			foreach (AntiXray::getPacketsFromBatch($batch) as $packet) {
				$chunkPacket = PacketPool::getPacket($packet);
				if ($chunkPacket instanceof LevelChunkPacket) {
					$chunkPacket->decode();
					Server::getInstance()->getAsyncPool()->submitTask(new ChunkModificationTask($event->getPlayer()->getLevel()->getChunk($chunkPacket->getChunkX(), $chunkPacket->getChunkZ()), $event->getPlayer()));
					$event->setCancelled();
				}
			}
			
		}
	}
	
	/**
	 * @param BlockBreakEvent $event
	 *
	 * @ignoreCancelled false
	 */
	public function onBreak(BlockBreakEvent $event) {
		if ($event->isCancelled()) return;
		$players = $event->getBlock()->getLevel()->getChunkPlayers($event->getBlock()->getFloorX() >> 4, $event->getBlock()->getFloorZ() >> 4);
		
		$blocks = AntiXray::getInvolvedBlocks([$event->getBlock()->asVector3()]);
		
		$event->getPlayer()->getLevel()->sendBlocks($players, $blocks, UpdateBlockPacket::FLAG_NEIGHBORS);
	}
	
	/**
	 * @param EntityExplodeEvent $event
	 *
	 * @ignoreCancelled false
	 */
	public function onExplode(EntityExplodeEvent $event) {
		if ($event->isCancelled()) return;
		Server::getInstance()->getAsyncPool()->submitTask(new BlockCalculationTask(array_map(function($block) {
			return $block->asVector3();
		}, $event->getBlockList()), $event->getEntity()->getLevelNonNull()->getFolderName()));
	}
}