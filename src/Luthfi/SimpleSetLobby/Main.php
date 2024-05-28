<?php

declare(strict_types=1);

namespace Luthfi\SimpleSetLobby;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class Main extends PluginBase {

    private Config $config;

    public function onEnable() : void {
        $this->getLogger()->info("SetLobby enabled");
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
    }

    public function onDisable() : void {
        $this->getLogger()->info("SetLobby disabled");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case "setlobby":
                    if ($sender->hasPermission("setlobby.cmd")) {
                        $this->setLobby($sender);
                    } else {
                        $sender->sendMessage("You do not have permission to use this command.");
                    }
                    return true;
                case "lobbyui":
                    if ($sender->hasPermission("lobbyui.cmd")) {
                        $this->openLobbyUI($sender);
                    } else {
                        $sender->sendMessage("You do not have permission to use this command.");
                    }
                    return true;
            }
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return false;
    }

    private function setLobby(Player $player): void {
        $pos = $player->getPosition();
        $this->config->set("lobby", [
            "x" => $pos->getX(),
            "y" => $pos->getY(),
            "z" => $pos->getZ(),
            "world" => $player->getWorld()->getFolderName()
        ]);
        $this->config->save();
        $player->sendMessage("Lobby location set!");
    }

    private function unsetLobby(Player $player): void {
        $this->config->remove("lobby");
        $this->config->save();
        $player->sendMessage("Lobby location unset!");
    }

    public function teleportToLobby(Player $player): void {
        $lobby = $this->config->get("lobby");
        if ($lobby === null) {
            $player->sendMessage("Lobby location is not set.");
            return;
        }

        $world = $this->getServer()->getWorldManager()->getWorldByName($lobby["world"]);
        if (!$world instanceof World) {
            $player->sendMessage("Lobby world not found.");
            return;
        }

        $pos = new Position($lobby["x"], $lobby["y"], $lobby["z"], $world);
        $player->teleport($pos);
        $player->sendMessage("Teleported to the lobby!");
    }

    private function openLobbyUI(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    if ($player->hasPermission("setlobby.ui.set")) {
                        $this->setLobby($player);
                    } else {
                        $player->sendMessage("You do not have permission to set the lobby.");
                    }
                    break;
                case 1:
                    if ($player->hasPermission("unsetlobby.cmd")) {
                        $this->unsetLobby($player);
                    } else {
                        $player->sendMessage("You do not have permission to unset the lobby.");
                    }
                    break;
            }
        });

        $form->setTitle("LobbyUI");
        $form->setContent("Select an option:");
        $form->addButton("SetLobby");
        $form->addButton("UnSetLobby");
        $player->sendForm($form);
    }
}
