<?php

namespace HimmelKreis4865\AntiXray;

use pocketmine\block\Block;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class QueueUpdateTask extends Task {

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void {
        foreach (AntiXray::getInstance()->blockQueue as $level => $data) {
            if(empty($data)) continue;
            $blocks = [];
            $int = 0;
            $level = Server::getInstance()->getLevelByName($level);
            foreach ($data as $key => $block) {
                if($int > 20) break;
                $blocks[] = $level->getBlock($block);
                unset(AntiXray::getInstance()->blockQueue[$level->getFolderName()][$key]);
                $int++;
            }

            foreach ($level->getPlayers() as $player) $player->sendTip("§r§a" . count($blocks));
            if(count($blocks) <= 0 || is_null($level)) continue;
            foreach ($level->getPlayers() as $player) {
                $player->getLevel()->sendBlocks([$player], $blocks, UpdateBlockPacket::FLAG_ALL_PRIORITY);
            }
        }
    }
}