<?php

/**
 * BadWordBlocker | general filter class
 */

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;

abstract class Filter
{
    /**
     * Check message if it violates the filter
     *
     * @param  \pocketmine\player\Player  $pl
     * @param  string  $message
     *
     * @return bool true = violation found; false = message ok, no violation
     */
    abstract public function check(Player $pl, string $message): bool;

    /**
     * Handle violation of the filter
     *
     * @param  \pocketmine\player\Player  $pl
     * @param  string  $originalMessage
     *
     * @return void
     */
    abstract public function action(Player $pl, string $originalMessage): void;

    /**
     * Save the message for comparing on next check (e.g. for duplicate, time, etc.)
     *
     * @param  \pocketmine\player\Player  $pl
     * @param  string  $message
     *
     * @return void
     */
    public function saveMessage(Player $pl, string $message): void
    {
        // empty by default
    }

    /**
     * Reload config files used for filtering
     *
     * @return void
     */
    public function reloadConfig(): void
    {
        // empty by default
    }

    /**
     * Permission name to bypass the filter
     *
     * @return string
     */
    abstract public function getBypassPermission(): string;
}
