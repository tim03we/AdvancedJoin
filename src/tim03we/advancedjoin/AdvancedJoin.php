<?php

namespace tim03we\advancedjoin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\item\Item;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class AdvancedJoin extends PluginBase implements Listener {

    public function configUpdater(): void {
        $settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
		if($settings->get("version") !== "1.0.9"){
			rename($this->getDataFolder() . "settings.yml", $this->getDataFolder() . "settings_old.yml");
			$this->saveResource("settings.yml");
            $this->getLogger()->notice("We create a new settings.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as settings_old.yml.");
		}
	}

    public function onEnable(){
        $this->configUpdater();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("settings.yml");
        $settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
    }
	
    public function onJoin (PlayerJoinEvent $event) {
        $settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        $player = $event->getPlayer();
        $name = $player->getName();
        $playerrep = $name;
        $getarray = array();
        if($settings->get("Spawn-Point") == "") {
            $this->getLogger()->debug("Since no spawn point was set in the config, the player is not teleported.");
        } else {
            $this->getServer()->loadLevel($settings->get("Spawn-Point"));
            $player->teleport($this->getServer()->getLevelByName($settings->get("Spawn-Point"))->getSafeSpawn());
        }
        foreach($settings->get("Commands") as $command) {
            Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $this->convert($command, $playerrep));
        }
        if($settings->get("Invetory-Clear") == "true") {
            $player->getInventory()->clearAll();
        } else if($settings->get("Health") == "true") {
            $player->setHealth(20);
        } else if($settings->get("Feed") == "true") {
            $player->setFood(20);
        } else if($settings->get("Welcome-Message" == "")) {
            $this->getLogger()->debug("The player will not receive a welcome message because none has been set in the config.");
        } else {
            $player->sendMessage($settings->get("Welcome-Message"));
        }
        if($settings->get("Enable-JoinMessage") == "true") {
            if($player->isOp(true)) {
                $event->setJoinMessage($this->convert($settings->get("JoinMessage-OP"), $playerrep));
            } else {
                if($player->hasPermission("staff.join")) {
                    $event->setJoinMessage($this->convert($settings->get("JoinMessage-Staff"), $playerrep));
                } else {
                    $event->setJoinMessage($this->convert($settings->get("JoinMessage"), $playerrep));
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        $player = $event->getPlayer();
        $name = $player->getName();
        $playerrep = $name;
        if($settings->get("Enable-QuitMessage") == "true") {
            if($player->isOp(true)) {
                $event->setQuitMessage($this->convert($settings->get("QuitMessage-OP"), $playerrep));
            } else {
                if($player->hasPermission("staff.quit")) {
                    $event->setQuitMessage($this->convert($settings->get("QuitMessage-Staff"), $playerrep));
                } else {
                    $event->setQuitMessage($this->convert($settings->get("QuitMessage"), $playerrep));
                }
            }
        }
    }

    public function convert(string $string, $playerrep): string{
        $string = str_replace("{player}", $playerrep, $string);
        return $string;
	}
}