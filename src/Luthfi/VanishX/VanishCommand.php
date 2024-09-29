<?php

namespace Luthfi\VanishX;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class VanishCommand extends Command implements PluginOwned {
    use PluginOwnedTrait;

    private $plugin;

    public function __construct(BetterVanish $plugin) {
        parent::__construct("vanish", "Toggle vanish mode", "/vanish", ["v", "vanishlist"]);
        $this->plugin = $plugin;
        $this->setPermission("vanishx.use");

        $this->owningPlugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if (!$sender->hasPermission("vanishx.use")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command.");
            return false;
        }

        if (isset($args[0]) && $args[0] === "list") {
            $this->sendVanishedList($sender);
            return true;
        }

        $isVanished = $this->plugin->isVanished($sender);
        $this->plugin->setVanished($sender, !$isVanished);

        $config = $this->plugin->getConfig();
        $prefix = $config->get("prefix", "[BetterVanish] ");

        if ($isVanished) {
            $sender->sendMessage(TF::colorize($prefix . $config->get("unvanish-message", "§aYou are now visible!")));
            if ($config->get("fake-join-leave-message", true)) {
                $this->plugin->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $sender->getName(), $config->get("fake-join-message", "§e{player} joined the game."))));
            }
        } else {
            $sender->sendMessage(TF::colorize($prefix . $config->get("vanish-message", "§aYou are now vanished!")));
            if ($config->get("fake-join-leave-message", true)) {
                $this->plugin->getServer()->broadcastMessage(TF::colorize(str_replace("{player}", $sender->getName(), $config->get("fake-leave-message", "§e{player} left the game."))));
            }

            $sender->sendActionBarMessage(TF::colorize("§fYou are currently §bVANISHED"));
        }

        return true;
    }

    private function sendVanishedList(CommandSender $sender): void {
        $vanishedPlayers = $this->plugin->getVanishedPlayers();
        if (empty($vanishedPlayers)) {
            $sender->sendMessage("§aNo players are currently vanished.");
        } else {
            $sender->sendMessage("§bCurrently vanished players: " . implode(", ", $vanishedPlayers));
        }
    }
}
