<?php

/* 
 * Plugin developed by SURVA.ml Dev Team.
 * Homepage: www.surva.ml - Mail: support@surva.ml
 */

namespace jjmc\BadWordBlocker;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class BadWordBlocker extends PluginBase implements Listener {
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();

        $this->list = $this->getConfig()->get("badwords");
        $this->list = explode(',', $this->list);
        $this->blockmessage = $this->getConfig()->get("blockmessage");
    }
    
    public function onPlayerChat(PlayerChatEvent $event) {
        $message = $event->getMessage();
        $player = $event->getPlayer();
        
        if ($this->contains($message, $this->list)) {
            $player->sendMessage($this->blockmessage);
            $event->setCancelled(true);
        }
    }

    public function contains($wort, array $liste) {
        foreach ($liste as $item) {
            if (strpos(strtolower($wort),$item) !== FALSE) {
                return true;
            }
        }
        return false;
    }
}