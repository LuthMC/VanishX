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
            $this->vanishedPlayers[] = $player->getName();
            $player->setInvisible(true);
            $player->setFlying(true);
            $player->setAllowFlight(true);
            $player->setMovementSpeed($this->getConfig()->get("vanish-fly-speed", 0.1));
        } else {
            $this->vanishedPlayers = array_diff($this->vanishedPlayers, [$player->getName()]);
            $player->setInvisible(false);
            $player->setFlying(false);
            $player->setAllowFlight(false);
            $player->setMovementSpeed(0.1);
        }

        $messageKey = $vanish ? "notify-vanish-message" : "notify-unvanish-message";
        $message = str_replace("{player}", $player->getName(), $this->getConfig()->get($messageKey, ""));

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer->hasPermission("bettervanish.other")) {
                $onlinePlayer->sendMessage($message);
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        if (!$this->getConfig()->get("break-block", true)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        if (!$this->getConfig()->get("break-block", true)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        if (!$this->getConfig()->get("break-block", true)) {
            return;
        }

        $player = $event->getPlayer();
        if ($this->isVanished($player)) {
            $event->cancel();
        }
    }
}
