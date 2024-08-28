<?php

namespace Luthfi\BetterVanish;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat as TF;

class BetterVanish extends PluginBase implements Listener {

    private $vanishedPlayers = [];

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->checkConfigVersion();

        $this->getServer()->getCommandMap()->register("vanish", new VanishCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    private function checkConfigVersion(): void {
        $configVersion = $this->getConfig()->get("config-version", "1.0.0");
        if ($configVersion !== "1.0.0") {
            $this->getLogger()->warning("Your config.yml is outdated. Please update it to the latest version.");
        }
    }

    public function isVanished(Player $player): bool {
        return in_array($player->getName(), $this->vanishedPlayers);
    }

    public function setVanished(Player $player, bool $vanish): void {
    if ($vanish) {
        $this->vanishedPlayers[$player->getName()] = $player;
        $player->setInvisible(true);

        if ($player->isCreative()) {
            $player->setAllowFlight(true);
        } else {
            $player->setFlying(true);
        }

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if (!$onlinePlayer->hasPermission("bettervanish.other")) {
                $onlinePlayer->hidePlayer($player);
            }
        }

        $player->sendMessage(TF::colorize($this->getConfig()->get("prefix", "[BetterVanish] ") . $this->getConfig()->get("vanish-message", "&aYou are now vanished!")));

        if ($this->getConfig()->get("fake-join-leave-message", true)) {
            $this->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $player->getName(), $this->getConfig()->get("fake-leave-message", "&e{player} left the game."))));
        }
    } else {
        unset($this->vanishedPlayers[$player->getName()]);
        $player->setInvisible(false);

        if ($player->isCreative()) {
            $player->setAllowFlight(true);
        } else {
            $player->setFlying(false);
        }

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer->showPlayer($player);
        }

        $player->sendMessage(TF::colorize($this->getConfig()->get("prefix", "[BetterVanish] ") . $this->getConfig()->get("unvanish-message", "&aYou are now visible!")));
        if ($this->getConfig()->get("fake-join-leave-message", true)) {
            $this->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $player->getName(), $this->getConfig()->get("fake-join-message", "&e{player} joined the game."))));
        }
    }
}

    public function onBlockBreak(BlockBreakEvent $event): void {
        if (!$this->getConfig()->get("break-block", false)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        if (!$this->getConfig()->get("break-block", false)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        if (!$this->getConfig()->get("break-block", false)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }
}
