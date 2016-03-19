<?php

/* 
 * Plugin developed by SURVA.ml Dev Team.
 * Homepage: www.surva.ml - Mail: support@surva.ml
 */

namespace jjmc\BadWordBlocker;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
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

        $this->chaton = $this->getConfig()->get("chaton");
        $this->chatoff = $this->getConfig()->get("chatoff");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch(strtolower($command->getName())) {
            case "chat":
                if(isset($sender->nochat)) {
                    unset($sender->nochat);
                    $sender->sendMessage($this->chaton);
                } else {
                    $sender->nochat = true;
                    $sender->sendMessage($this->chatoff);
                }
                return true;
        }
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $message = $event->getMessage();
        $player = $event->getPlayer();
        
        if($this->contains($message, $this->list)) {
            $player->sendMessage($this->blockmessage);
            $event->setCancelled(true);
            return;
        }

        if(isset($player->lastwritten)) {
            if($player->lastwritten == $message) {
                $player->sendMessage($this->lastwritten);
                $event->setCancelled(true);
                return;
            }
        }

        if(isset($player->timewritten)) {
            if($player->timewritten > new \DateTime()) {
                $player->sendMessage($this->timewritten);
                $event->setCancelled(true);
                return;
            }
        }

        if(ctype_upper($message)) {
            $player->sendMessage($this->caps);
            $event->setCancelled(true);
            return;
        }

        $player->timewritten = new \DateTime();
        $player->timewritten = $player->timewritten->add(new \DateInterval("PT".$this->waitingtime."S"));
        $player->lastwritten = $message;

        $recipients = $event->getRecipients();
        $newrecipients = array();

        foreach($recipients as $recipient) {
            if(!isset($recipient->nochat)) {
                $newrecipients[] = $recipient;
            }
        }

        $event->setRecipients($newrecipients);
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