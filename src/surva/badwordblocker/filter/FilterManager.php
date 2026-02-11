<?php

/**
 * BadWordBlocker | main component for communication with filters, registers
 * all filters and provides methods to check messages against them, handle
 * violations, etc.
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\BadWordBlocker;
use surva\badwordblocker\util\Messages;

class FilterManager
{
    private BadWordBlocker $badWordBlocker;

    /**
     * @var Filter[] filters to check against
     */
    private array $filters;
    /**
     * @var bool[] players to which the bypassed message was already sent during this runtime
     */
    private array $bypassedMessageSent;
    /**
     * @var int[] violations counter for players
     */
    private array $playersViolations;

    public function __construct(BadWordBlocker $badWordBlocker)
    {
        $this->badWordBlocker = $badWordBlocker;

        $this->bypassedMessageSent = [];
        $this->playersViolations = [];

        $this->registerFilters();
    }

    /**
     * Check a message against all filters, returns true if a violation
     * was found, false if the message is ok
     *
     * @param Player $player
     * @param string $message
     *
     * @return bool
     */
    public function checkMessage(Player $player, string $message): bool
    {
        if ($this->badWordBlocker->getConfig()->get("ignorespaces", true) === true) {
            $message = str_replace(" ", "", $message);
        }

        foreach ($this->filters as $filter) {
            $checkResult = $filter->check($player, $message);
            $filter->saveMessage($player, $message);

            if ($checkResult) {
                if ($player->hasPermission($filter->getBypassPermission())) {
                    $this->sendBypassedMessage($player);
                } else {
                    $filter->action($player, $message);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Register a filter violation and handle the punishment, e.g. kick
     * or ban the player if configured
     *
     * @param Player $player
     *
     * @return void
     */
    public function handleViolation(Player $player): void
    {
        $playerName = $player->getName();

        if (!isset($this->playersViolations[$playerName])) {
            $this->playersViolations[$playerName] = 0;
        }

        $this->playersViolations[$playerName]++;

        $violKick = $this->badWordBlocker->getConfig()->getNested("violations.kick", 0);
        $violBan = $this->badWordBlocker->getConfig()->getNested("violations.ban", 0);
        $resetAfterKick = $this->badWordBlocker->getConfig()->getNested("violations.resetafterkick", true);

        $translMessages = new Messages($this->badWordBlocker, $player);

        if ($this->playersViolations[$playerName] === $violKick) {
            $player->kick($translMessages->getMessage("punishment.kick"));

            if ($resetAfterKick) {
                $this->playersViolations[$playerName] = 0;
            }
        } elseif ($this->playersViolations[$playerName] === $violBan) {
            $this->badWordBlocker->getServer()->getNameBans()->addBan(
                $playerName,
                $translMessages->getMessage("punishment.ban")
            );
            $player->kick($translMessages->getMessage("punishment.ban"));

            $this->playersViolations[$playerName] = 0;
        }
    }

    /**
     * Send filter bypassed message to a player if not already
     * sent during this session
     *
     * @param Player $pl
     *
     * @return void
     */
    private function sendBypassedMessage(Player $pl): void
    {
        if ($this->badWordBlocker->getConfig()->get("send_bypassed_message", true) !== true) {
            return;
        }

        $id = $pl->getId();

        if (isset($this->bypassedMessageSent[$id])) {
            return;
        }

        $this->badWordBlocker->sendMessage($pl, "filter_bypassed");
        $this->bypassedMessageSent[$id] = true;
    }

    /**
     * Register filters to check messages against
     *
     * @return void
     */
    public function registerFilters(): void
    {
        $this->filters = [
          new SwearWordFilter($this),
          new DuplicateFilter($this),
          new SpeedFilter($this),
          new CapsLockFilter($this),
          new WebAddressFilter($this),
        ];
    }

    /**
     * Instruct filters to reload their config files
     *
     * @return void
     */
    public function reloadFilterConfigs(): void
    {
        foreach ($this->filters as $filter) {
            $filter->reloadConfig();
        }
    }

    /**
     * @return BadWordBlocker
     */
    public function getBadWordBlocker(): BadWordBlocker
    {
        return $this->badWordBlocker;
    }
}
