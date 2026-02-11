<?php

/**
 * BadWordBlocker | swear word filter, checks if the message
 * contains any of the blocked words
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class SwearWordFilter extends Filter
{
    private FilterManager $filterManager;

    /**
     * @var string[] raw list of blocked words
     */
    private array $blockedWords;

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

        $blocked = null;
        if ($this->filterManager->getBadWordBlocker()->getConfig()->get("showblocked") === true) {
            $blocked = $this->contains($originalMessage, $this->blockedWords);
        }

        $msg = $blocked
          ? $messages->getMessage("blocked.messagewithblocked", ["blocked" => $blocked])
          : $messages->getMessage("blocked.message");
        $pl->sendMessage($msg);

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
     * @param string $string
     * @param string[] $contains
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
     * Load swear word list from config file
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
