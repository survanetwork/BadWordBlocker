<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 06.10.16
 * Time: 18:05
 */

namespace surva\badwordblocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class EventListener implements Listener {
    /* @var BadWordBlocker */
    private $badWordBlocker;

    public function __construct(BadWordBlocker $badWordBlocker) {
        $this->badWordBlocker = $badWordBlocker;
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if($this->getBadWordBlocker()->contains($message, $this->getBadWordBlocker()->getList())) {
            $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("blockmessage"));
            $event->setCancelled(true);

            return;
        }

        if(isset($player->lastwritten)) {
            if($player->lastwritten == $message) {
                $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("lastwritten"));
                $event->setCancelled(true);

                return;
            }
        }

        if(isset($player->timewritten)) {
            if($player->timewritten > new \DateTime()) {
                $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("timewritten"));
                $event->setCancelled(true);

                return;
            }
        }

        if(
			($this->getBadWordBlocker()->countUppercaseChars($message) / strlen($message)) >= $this->getBadWordBlocker()->getConfig()->get("uppercasepercentage")
			&& strlen($message) >= $this->getBadWordBlocker()->getConfig()->get("minimumchars")
		){
            $player->sendMessage($this->getBadWordBlocker()->getConfig()->get("caps"));
            $event->setCancelled(true);

            return;
        }

        $player->timewritten = new \DateTime();
        $player->timewritten = $player->timewritten->add(new \DateInterval("PT" . $this->getBadWordBlocker()->getConfig()->get("waitingtime") . "S"));
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

    /**
     * @return BadWordBlocker
     */
    public function getBadWordBlocker(): BadWordBlocker {
        return $this->badWordBlocker;
    }
}