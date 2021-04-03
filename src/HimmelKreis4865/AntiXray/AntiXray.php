<?php

namespace HimmelKreis4865\AntiXray;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use ReflectionProperty;
use function array_diff;
use function array_map;
use function var_dump;

class AntiXray extends PluginBase {
	/** @var null | self $instance */
	protected static $instance = null;
	
	/** @var int[] */
	public $ores = [];
	/** @var array  */
	public $blockQueue = [];
	
	/**
	 * Initialization call on startup
	 *
	 * @internal
	 */
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new QueueUpdateTask(), 1);
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
}