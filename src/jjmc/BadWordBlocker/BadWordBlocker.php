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
        $this->waitingtime = $this->getConfig()->get("waitingtime");

        $this->blockmessage = $this->getConfig()->get("blockmessage");
        $this->lastwritten = $this->getConfig()->get("lastwritten");
        $this->timewritten = $this->getConfig()->get("timewritten");
        $this->caps = $this->getConfig()->get("caps");
    }
    
    public function onPlayerChat(PlayerChatEvent $event) {
        $message = $event->getMessage();
        $player = $event->getPlayer();
        
        if($this->contains($message, $this->list)) {
            $player->sendMessage($this->blockmessage);
            $event->setCancelled(true);
        } elseif (isset($player->lastwritten)) {
            if($player->lastwritten == $message) {
                $player->sendMessage($this->lastwritten);
                $event->setCancelled(true);
            }
        } elseif(isset($player->timewritten)) {
            if($player->timewritten > new \DateTime()) {
                $player->sendMessage($this->timewritten);
                $event->setCancelled(true);
            }
        } elseif(ctype_upper($message)) {
            $player->sendMessage($this->caps);
            $event->setCancelled(true);
        }

        if(!$event->isCancelled()) {
            //$player->timewritten = date_add(new \DateTime(), new \DateInterval("PT".$this->waitingtime."S"));
            $player->timewritten = new \DateTime();
            $player->timewritten = $player->timewritten->add(new \DateInterval("PT".$this->waitingtime."S"));
            $player->lastwritten = $message;
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