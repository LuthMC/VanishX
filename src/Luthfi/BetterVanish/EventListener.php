<?php

namespace Luthfi\BetterVanish;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

    private $plugin;

    public function __construct(BetterVanish $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->isVanished($player)) {
            $event->setJoinMessage("");
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->isVanished($player)) {
            $event->setQuitMessage("");
        }
    }
}
