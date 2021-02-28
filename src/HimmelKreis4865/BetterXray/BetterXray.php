<?php

namespace HimmelKreis4865\BetterXray;

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
}