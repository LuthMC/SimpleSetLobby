<?php

declare(strict_types=1);

namespace Luthfi\SimpleSetLobby;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener {

    private $config;

    public function onEnable() : void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("SetLobby plugin enabled.");
    }

    public function onDisable() : void {
        $this->getLogger()->info("SetLobby plugin disabled.");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        switch ($command->getName()) {
            case "setlobby":
                if ($sender->hasPermission("setlobby.cmd")) {
                    $this->setLobby($sender);
                    $sender->sendMessage("Lobby location set.");
                } else {
                    $sender->sendMessage("You do not have permission to use this command.");
                }
                return true;

            case "lobby":
                if ($sender->hasPermission("setlobby.cmd")) {
                    $this->teleportToLobby($sender);
                } else {
                    $sender->sendMessage("You do not have permission to use this command.");
                }
                return true;

            default:
                return false;
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $this->teleportToLobby($player);
    }

    private function setLobby(Player $player) : void {
        $world = $player->getWorld()->getFolderName();
        $x = $player->getPosition()->getX();
        $y = $player->getPosition()->getY();
        $z = $player->getPosition()->getZ();
        $this->config->set("lobby", [
            "world" => $world,
            "x" => $x,
            "y" => $y,
            "z" => $z
        ]);
        $this->config->save();
    }

    private function teleportToLobby(Player $player) : void {
        $lobby = $this->config->get("lobby");
        if ($lobby === null || !isset($lobby["world"], $lobby["x"], $lobby["y"], $lobby["z"])) {
            $player->sendMessage("Lobby location is not set.");
            return;
        }

        $world = $this->getServer()->getWorldManager()->getWorldByName($lobby["world"]);
        if ($world === null) {
            $player->sendMessage("Lobby world is not loaded.");
            return;
        }

        $x = (float) $lobby["x"];
        $y = (float) $lobby["y"];
        $z = (float) $lobby["z"];
        $position = new Position($x, $y, $z, $world);

        $player->teleport($position);
        $player->sendMessage("Teleported to lobby.");
    }
}
