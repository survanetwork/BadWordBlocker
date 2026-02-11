<?php

/**
 * BadWordBlocker | caps lock (all uppercase) filter, counts
 * the percentage of uppercase chars in a message and compares
 * it to the configured limit
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class CapsLockFilter extends Filter
{
    private FilterManager $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @inheritDoc
     */
    public function check(Player $pl, string $message): bool
    {
        $uppercasePercentage = $this->filterManager->getBadWordBlocker()->getConfig()->get("uppercasepercentage", 0.75);
        $minimumChars = $this->filterManager->getBadWordBlocker()->getConfig()->get("minimumchars", 3);

        $messageLength = strlen($message);

        return $messageLength > $minimumChars and ($this->countUppercaseChars(
            $message
        ) / $messageLength) >= $uppercasePercentage;
    }

    /**
     * @inheritDoc
     */
    public function action(Player $pl, string $originalMessage): void
    {
        $messages = new Messages($this->filterManager->getBadWordBlocker(), $pl);

        $pl->sendMessage($messages->getMessage("blocked.caps"));
        $this->filterManager->handleViolation($pl);
    }

    /**
     * Counts uppercase chars in a string
     *
     * @param string $string
     *
     * @return int
     */
    private function countUppercaseChars(string $string): int
    {
        preg_match_all("/[A-Z]/", $string, $matches);

        return count($matches[0]);
    }

    /**
     * @inheritDoc
     */
    public function getBypassPermission(): string
    {
        return "badwordblocker.bypass.caps";
    }
}
