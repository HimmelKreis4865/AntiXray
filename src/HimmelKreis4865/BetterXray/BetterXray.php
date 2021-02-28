<?php

namespace HimmelKreis4865\BetterXray;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\plugin\PluginBase;

class BetterXray extends PluginBase {
	/**
	 * Initialization call on startup
	 *
	 * @internal
	 */
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}
	
	/**
	 * Returns a Generator array with all packets inside the batch (no limit)
	 *
	 * @api
	 *
	 * @param BatchPacket $packet
	 */
	final public static function getPacketsFromBatch(BatchPacket $packet) {
		$stream = new NetworkBinaryStream($packet->payload);
		while(!$stream->feof()){
			yield $stream->getString();
		}
	}
}