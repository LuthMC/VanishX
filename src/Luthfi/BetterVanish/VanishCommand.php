<?php

namespace Luthfi\BetterVanish;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class VanishCommand extends Command {

    private $plugin;

    public function __construct(BetterVanish $plugin) {
        parent::__construct("vanish", "Toggle vanish mode", "/vanish", ["v"]);
        $this->plugin = $plugin;
        $this->setPermission("bettervanish.use");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if (!$sender->hasPermission("bettervanish.use")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command.");
            return false;
        }

        $isVanished = $this->plugin->isVanished($sender);
        $this->plugin->setVanished($sender, !$isVanished);

        $config = $this->plugin->getConfig();
        $prefix = $config->get("prefix", "[BetterVanish] ");

        if ($isVanished) {
            $sender->sendMessage(TF::colorize($prefix . $config->get("unvanish-message", "&aYou are now visible!")));
            if ($config->get("fake-join-leave-message", true)) {
                $this->plugin->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $sender->getName(), $config->get("fake-join-message", "&e{player} joined the game."))));
            }
        } else {
            $sender->sendMessage(TF::colorize($prefix . $config->get("vanish-message", "&aYou are now vanished!")));
            if ($config->get("fake-join-leave-message", true)) {
                $this->plugin->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $sender->getName(), $config->get("fake-leave-message", "&e{player} left the game."))));
            }
        }

        return true;
    }
}
