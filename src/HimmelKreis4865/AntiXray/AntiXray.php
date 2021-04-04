<?php

namespace HimmelKreis4865\AntiXray;

use HimmelKreis4865\AntiXray\tasks\ChunkModificationTask;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use ReflectionProperty;
use Volatile;
use function array_diff;
use function array_map;

class AntiXray extends PluginBase {
	/** @var null | self $instance */
	protected static $instance = null;
	
	/** @var int[] */
	public $ores = [];
	
	/**
	 * Initialization call on startup
	 *
	 * @internal
	 */
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		self::$instance = $this;
		$this->initConfig();
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
	
	/**
	 * Returns an instance of the plugin
	 *
	 * @api
	 *
	 * @return AntiXray|null
	 */
	public static function getInstance(): ?AntiXray {
		return self::$instance;
	}
	
	public function initConfig(): void {
		$ref = new ReflectionClass($this);
		$properties = array_diff(array_map(function(ReflectionProperty $property) {
			return $property->getName();
		}, $ref->getProperties()), array_map(function(ReflectionProperty $property) {
			return $property->getName();
		}, $ref->getParentClass()->getProperties()), array_keys($ref->getStaticProperties()));
		
		foreach ($this->getConfig()->getAll() as $key => $value) {
			if (in_array($key, $properties)) $this->{$key} = $value;
		}
	}
	
	/**
	 * Returns an array with all blocks that are in sides of the blocks in parameter 1
	 *
	 * @api
	 *
	 * @param Vector3[]|Volatile $blocks
	 *
	 * @return Vector3[]
	 */
	public static function getInvolvedBlocks($blocks): array {
		$finalBlocks = [];
		
		foreach ($blocks as $key => $block) {
			$finalBlocks[] = $block;
			foreach (ChunkModificationTask::BLOCK_SIDES as $side) {
				$side = $blocks[$key]->getSide($side);
				
				foreach (ChunkModificationTask::BLOCK_SIDES as $side_2)
					$finalBlocks[] = $side->getSide($side_2);
				
				$finalBlocks[] = $side;
			}
		}
		return $finalBlocks;
	}
}