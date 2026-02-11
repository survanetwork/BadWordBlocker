<?php

/**
 * BadWordBlocker | event listener to check chat messages, private message
 * commands and sign texts against filters
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
     * Listen for tell command and its aliases, check the message against the filters
     * and cancel if it doesn't pass the check
     *
     * @param CommandEvent $event
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
     * Check chat message against filters and cancel
     * if it don't pass the check
     *
     * @param PlayerChatEvent $event
     *
     * @return void
     */
    public function onPlayerChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if ($this->badWordBlocker->getFilterManager()->checkMessage($player, $message)) {
            $event->cancel();
        }
    }

    /**
     * Check text on placed or edited sings against filters, cancel if
     * it don't pass the check
     *
     * @param SignChangeEvent $event
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
