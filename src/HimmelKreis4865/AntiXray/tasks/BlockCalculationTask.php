<?php

namespace HimmelKreis4865\AntiXray\tasks;

use HimmelKreis4865\AntiXray\AntiXray;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Volatile;
use function array_chunk;

class BlockCalculationTask extends AsyncTask {
	
	/** @var Volatile|Vector3[] $blocks */
	protected $blocks;
	
	/** @var string $level */
	protected $level;
	
	public function __construct(array $blocks, string $level) {
		$this->blocks = $blocks;
		$this->level = $level;
	}
	
	public function onRun(): void {
		if (count($this->blocks) === 0) return;
		$this->setResult(AntiXray::getInvolvedBlocks($this->blocks));
	}
	
	public function onCompletion(Server $server) {
		if (!$this->hasResult() or !Server::getInstance()->isLevelLoaded($this->level)) return;
		$players = Server::getInstance()->getLevelByName($this->level)->getChunkPlayers((($pos = $this->getResult()[array_rand($this->getResult())])->getX() >> 4), ($pos->getZ() >> 4));
		foreach (array_chunk($this->getResult(), 450) as $blocks) {
			Server::getInstance()->getLevelByName($this->level)->sendBlocks($players, $blocks, UpdateBlockPacket::FLAG_NEIGHBORS);
		}
	}
}