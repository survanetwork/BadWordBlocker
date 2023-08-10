<?php

namespace surva\badwordblocker\filter;

use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class WebAddressFilter extends Filter
{
    private const IP_REGEX = "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/";
    private const DOMAIN_REGEX = "/[A-Za-z0-9\-]*\.(com|net|org|io|me|us|de|nl|tk)/";
    private const EMAIL_REGEX = "/[A-Za-z0-9\-_.]*@[A-Za-z0-9\-]*\.[a-z]{2,4}/";

    private FilterManager $filterManager;

    /**
     * @param  \surva\badwordblocker\filter\FilterManager  $filterManager
     */
    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @inheritDoc
     */
    public function check(Player $pl, string $message): bool
    {
        if ($this->filterManager->getBadWordBlocker()->getConfig()->get("filter_web_addresses", true) !== true) {
            return false;
        }

        $ip = preg_match(self::IP_REGEX, $message);
        $domain = preg_match(self::DOMAIN_REGEX, $message);
        $email = preg_match(self::EMAIL_REGEX, $message);

        return $ip !== 0 || $domain !== 0 || $email !== 0;
    }

    /**
     * @inheritDoc
     */
    public function action(Player $pl, string $originalMessage): void
    {
        $messages = new Messages($this->filterManager->getBadWordBlocker(), $pl);

        $pl->sendMessage($messages->getMessage("blocked.address"));
        $this->filterManager->handleViolation($pl);
    }

    /**
     * @inheritDoc
     */
    public function getBypassPermission(): string
    {
        return "badwordblocker.bypass.address";
    }
}
