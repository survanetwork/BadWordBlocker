<?php

/**
 * BadWordBlocker | swear word filter
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class SwearWordFilter extends Filter
{
    private FilterManager $filterManager;
    private array $blockedWords;

    /**
     * @param  \surva\badwordblocker\filter\FilterManager  $filterManager
     */
    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;

        $this->loadConfig();
    }

    /**
     * @inheritDoc
     */
    public function check(Player $pl, string $message): bool
    {
        return $this->contains($message, $this->blockedWords) !== null;
    }

    /**
     * @inheritDoc
     */
    public function action(Player $pl, string $originalMessage): void
    {
        $messages = new Messages($this->filterManager->getBadWordBlocker(), $pl);

        if ($this->filterManager->getBadWordBlocker()->getConfig()->get("showblocked") === true) {
            $blocked = $this->contains($originalMessage, $this->blockedWords);

            $pl->sendMessage(
                $messages->getMessage("blocked.messagewithblocked", ["blocked" => $blocked])
            );
        } else {
            $pl->sendMessage($messages->getMessage("blocked.message"));
        }

        $this->filterManager->handleViolation($pl);
    }

    /**
     * @inheritDoc
     */
    public function reloadConfig(): void
    {
        $this->loadConfig();
    }

    /**
     * Check if a string contains a specific string from an array and return it
     *
     * @param  string  $string
     * @param  array  $contains
     *
     * @return string|null
     */
    private function contains(string $string, array $contains): ?string
    {
        $ignoreSpaces = $this->filterManager->getBadWordBlocker()->getConfig()->get("ignorespaces", true) === true;

        foreach ($contains as $contain) {
            if (
                str_contains(
                    strtolower($string),
                    $ignoreSpaces ? str_replace(" ", "", $contain) : $contain
                )
            ) {
                return $contain;
            }
        }

        return null;
    }

    /**
     * Load bad word list from config file
     *
     * @return void
     */
    public function loadConfig(): void
    {
        $this->blockedWords = $this->filterManager->getBadWordBlocker()->getConfig()->get(
            "badwords",
            ["fuck", "shit", "bitch"]
        );
    }

    /**
     * @inheritDoc
     */
    public function getBypassPermission(): string
    {
        return "badwordblocker.bypass.swear";
    }
}
