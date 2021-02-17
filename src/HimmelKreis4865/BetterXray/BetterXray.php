<?php

namespace HimmelKreis4865\BetterXray;

use pocketmine\plugin\PluginBase;

class BetterXray extends PluginBase {
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}
}