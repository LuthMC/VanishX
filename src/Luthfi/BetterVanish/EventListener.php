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

    public function onPlayerJoin(PlayerJoinEvent $event): void {
    $player = $event->getPlayer();
    $config = $this->plugin->getConfig();

    if ($config->get("automatic-vanish-on-join", true) && $player->hasPermission("bettervanish.use")) {
        $this->plugin->setVanished($player, true);
        $player->sendMessage(TF::colorize($config->get("prefix", "[BetterVanish] ") . $config->get("vanish-message", "&aYou are now vanished!")));
        if ($config->get("fake-join-leave-message", true)) {
            $this->plugin->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $player->getName(), $config->get("fake-leave-message", "&e{player} left the game."))));
        }
    }

    foreach ($this->plugin->getVanishedPlayers() as $vanishedPlayerName => $isVanished) {
        if ($isVanished) {
            $vanishedPlayer = $this->plugin->getServer()->getPlayerExact($vanishedPlayerName);
            if ($vanishedPlayer !== null && !$player->hasPermission("bettervanish.other")) {
                $player->hidePlayer($vanishedPlayer);
            }
        }
    }
}

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->isVanished($player)) {
            $event->setQuitMessage("");
        }
    }
}
