<?php

namespace tim03we\advancedjoin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class AdvancedJoin extends PluginBase implements Listener {

    public $settings;

    public function configUpdater(): void {
        if($this->settings->get("version") !== "1.2.0"){
            rename($this->getDataFolder() . "settings.yml", $this->getDataFolder() . "settings_old.yml");
            $this->saveResource("settings.yml");
            $this->getLogger()->notice("We create a new settings.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as settings_old.yml.");
        }
    }

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("settings.yml");
        $this->settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        $this->configUpdater();
    }

    public function onJoin (PlayerJoinEvent $event) {
        if($this->settings->get("Spawn-Point") == "") {
            $this->getLogger()->debug("Since no spawn point was set in the config, the player is not teleported.");
        } else {
            $this->getServer()->loadLevel($this->settings->get("Spawn-Point"));
            $event->getPlayer()->teleport($this->getServer()->getLevelByName($this->settings->get("Spawn-Point"))->getSafeSpawn());
        }
        if($this->settings->getNested("Commands.First-Join.enable") == true) {
            if($event->getPlayer()->hasPlayedBefore() == false) {
                foreach($this->settings->getNested("Commands.First-Join.commands") as $command) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $command));
                }
            }
        } else {
            foreach($this->settings->getNested("Commands.commands") as $command) {
                Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $command));
            }
        }
        if($this->settings->get("Invetory-Clear") == "true") {
            $event->getPlayer()->getPlayer()->getInventory()->clearAll();
        }
        if($this->settings->get("Health") == "true") {
            $event->getPlayer()->setHealth(20);
        }
        if($this->settings->get("Feed") == "true") {
            $event->getPlayer()->setFood(20);
        }
        if($this->settings->get("Welcome-Message" == "")) {
            $this->getLogger()->debug("The player will not receive a welcome message because none has been set in the config.");
        } else {
            $event->getPlayer()->sendMessage(str_replace("&", "§", $this->settings->get("Welcome-Message")));
        }
        if($this->settings->get("Enable-JoinMessage") == "true") {
            if($event->getPlayer()->isOp(true)) {
                $event->setJoinMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("JoinMessage-OP")));
            } else {
                if($event->getPlayer()->hasPermission("staff.join")) {
                    $event->setJoinMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("JoinMessage-Staff")));
                } else {
                    $event->setJoinMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("JoinMessage")));
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        if($this->settings->get("Enable-QuitMessage") == "true") {
            if($event->getPlayer()->isOp(true)) {
                $event->setQuitMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("QuitMessage-OP")));
            } else {
                if($event->getPlayer()->hasPermission("staff.quit")) {
                    $event->setQuitMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("QuitMessage-Staff")));
                } else {
                    $event->setQuitMessage(str_replace(["{player}", "&"], [$event->getPlayer()->getName(), "§"], $this->settings->get("QuitMessage")));
                }
            }
        }
    }
}