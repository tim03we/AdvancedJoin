<?php

namespace tim03we\advancedjoin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;

class AdvancedJoin extends PluginBase implements Listener {

    public function configUpdater(): void {
		if($this->cfg->get("version") !== "1.1.1"){
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
        $this->cfg = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
    }
	
    public function onJoin (PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $playerrep = $name;
        if($this->cfg->get("Spawn-Point") == "") {
            $this->getLogger()->debug("Since no spawn point was set in the config, the player is not teleported.");
        } else {
            $this->getServer()->loadLevel($this->cfg->get("Spawn-Point"));
            $player->teleport($this->getServer()->getLevelByName($this->cfg->get("Spawn-Point"))->getSafeSpawn());
        }
        foreach($this->cfg->get("Commands") as $command) {
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->convert($command, $playerrep));
        }
        if($this->cfg->get("Invetory-Clear") == "true") {
            $player->getInventory()->clearAll();
        }
        if($this->cfg->get("Health") == "true") {
            $player->setHealth(20);
        }
        if($this->cfg->get("Feed") == "true") {
            $player->setFood(20);
        }
        if($this->cfg->get("Welcome-Message", !false)) {
            $player->sendMessage($this->cfg->get("Welcome-Message"));
        }
        if($this->cfg->get("Enable-JoinMessage") == "true") {
            if($player->isOp(true)) {
                $event->setJoinMessage($this->convert($this->cfg->get("JoinMessage-OP"), $playerrep));
            } else {
                if($player->hasPermission("staff.join")) {
                    $event->setJoinMessage($this->convert($this->cfg->get("JoinMessage-Staff"), $playerrep));
                } else {
                    $event->setJoinMessage($this->convert($this->cfg->get("JoinMessage"), $playerrep));
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $playerrep = $name;
        if($this->cfg->get("Enable-QuitMessage") == "true") {
            if($player->isOp(true)) {
                $event->setQuitMessage($this->convert($this->cfg->get("QuitMessage-OP"), $playerrep));
            } else {
                if($player->hasPermission("staff.quit")) {
                    $event->setQuitMessage($this->convert($this->cfg->get("QuitMessage-Staff"), $playerrep));
                } else {
                    $event->setQuitMessage($this->convert($this->cfg->get("QuitMessage"), $playerrep));
                }
            }
        }
    }

    public function convert(string $string, $playerrep): string{
        $string = str_replace("{player}", $playerrep, $string);
        return $string;
	}
}