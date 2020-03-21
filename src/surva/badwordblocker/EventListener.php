<?php
/**
 * BadWordBlocker | event listener
 */

namespace surva\badwordblocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class EventListener implements Listener {
    /* @var BadWordBlocker */
    private $badWordBlocker;

    /**
     * EventListener constructor
     *
     * @param BadWordBlocker $badWordBlocker
     */
    public function __construct(BadWordBlocker $badWordBlocker) {
        $this->badWordBlocker = $badWordBlocker;
    }

    /**
     * Cancel the tell command if the message doesn't pass the check
     *
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if(preg_match("/^\/tell (.*) (.*)/", $message, $result) === 1) {
            if(count($result) === 3) {
                if(!$this->badWordBlocker->checkMessage($player, $result[2])) {
                    $event->setCancelled();
                }
            }
        }
    }

    /**
     * Cancel a message if it doesn't pass the check
     *
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if(!$this->badWordBlocker->checkMessage($player, $message)) {
            $event->setCancelled();
        }
    }
}
