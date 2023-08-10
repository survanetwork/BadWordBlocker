<?php

/**
 * BadWordBlocker | event listener
 */

namespace surva\badwordblocker;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

class EventListener implements Listener
{
    private BadWordBlocker $badWordBlocker;

    public function __construct(BadWordBlocker $badWordBlocker)
    {
        $this->badWordBlocker = $badWordBlocker;
    }

    /**
     * Cancel the tell command if the message doesn't pass the check
     *
     * @param  \pocketmine\event\server\CommandEvent  $event
     *
     * @return void
     */
    public function onCommand(CommandEvent $event): void
    {
        $sender = $event->getSender();
        $command = $event->getCommand();

        if (!($sender instanceof Player)) {
            return;
        }

        $args = explode(" ", $command);

        if (count($args) < 2) {
            return;
        }

        $command = array_shift($args);

        switch ($command) {
            case "tell":
            case "w":
            case "msg":
                array_shift($args);
                break;
            case "me":
                break;
            default:
                return;
        }

        $realMessage = implode(" ", $args);

        if ($this->badWordBlocker->getFilterManager()->checkMessage($sender, $realMessage)) {
            $event->cancel();
        }
    }

    /**
     * Cancel a message if it doesn't pass the check
     *
     * @param  PlayerChatEvent  $event
     */
    public function onPlayerChat(PlayerChatEvent $event): void
    {
        $player  = $event->getPlayer();
        $message = $event->getMessage();

        if ($this->badWordBlocker->getFilterManager()->checkMessage($player, $message)) {
            $event->cancel();
        }
    }

    /**
     * Check placed signs if they violate filters
     *
     * @param  \pocketmine\event\block\SignChangeEvent  $event
     *
     * @return void
     */
    public function onSignChange(SignChangeEvent $event): void
    {
        if ($this->badWordBlocker->getConfig()->get("check_signs", true) !== true) {
            return;
        }

        $player = $event->getPlayer();
        $fullText = implode("", $event->getNewText()->getLines());

        if ($this->badWordBlocker->getFilterManager()->checkMessage($player, $fullText)) {
            $event->cancel();
        }
    }
}
