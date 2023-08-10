<?php

/**
 * BadWordBlocker | duplicate message filter
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class DuplicateFilter extends Filter
{
    private FilterManager $filterManager;
    private array $playersLastWritten;

    /**
     * @param  \surva\badwordblocker\filter\FilterManager  $filterManager
     */
    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
        $this->playersLastWritten = [];
    }

    /**
     * @inheritDoc
     */
    public function check(Player $pl, string $message): bool
    {
        $plName = $pl->getName();

        return isset($this->playersLastWritten[$plName]) && $this->playersLastWritten[$plName] === $message;
    }

    /**
     * @inheritDoc
     */
    public function action(Player $pl, string $originalMessage): void
    {
        $messages = new Messages($this->filterManager->getBadWordBlocker(), $pl);

        $pl->sendMessage($messages->getMessage("blocked.lastwritten"));
        $this->filterManager->handleViolation($pl);
    }

    /**
     * @inheritDoc
     */
    public function saveMessage(Player $pl, string $message): void
    {
        $this->playersLastWritten[$pl->getName()] = $message;
    }

    /**
     * @inheritDoc
     */
    public function getBypassPermission(): string
    {
        return "badwordblocker.bypass.same";
    }
}
