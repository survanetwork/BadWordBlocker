<?php

/**
 * BadWordBlocker | translated messages utils
 */

namespace surva\badwordblocker\util;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use surva\badwordblocker\BadWordBlocker;

class Messages
{
    private BadWordBlocker $badWordBlocker;

    private ?CommandSender $sender;

    public function __construct(BadWordBlocker $badWordBlocker, ?CommandSender $sender = null)
    {
        $this->badWordBlocker = $badWordBlocker;
        $this->sender = $sender;
    }

    /**
     * Get a translated message
     *
     * @param  string  $key
     * @param  array  $replaces
     *
     * @return string
     */
    public function getMessage(string $key, array $replaces = []): string
    {
        $prefLangId = null;

        if ($this->sender instanceof Player && $this->badWordBlocker->getConfig()->get("autodetectlanguage", true)) {
            preg_match("/^[a-z][a-z]/", $this->sender->getLocale(), $localeRes);

            if (isset($localeRes[0])) {
                $prefLangId = $localeRes[0];
            }
        }

        $defaultLangId = $this->badWordBlocker->getConfig()->get("language", "en");

        if ($prefLangId !== null) {
            $langConfig = $this->badWordBlocker->getTranslationMessages()[$prefLangId];
        } else {
            $langConfig = $this->badWordBlocker->getTranslationMessages()[$defaultLangId];
        }

        $rawMessage = $langConfig->getNested($key);

        if ($rawMessage === null || $rawMessage === "") {
            $rawMessage = $this->badWordBlocker->getDefaultMessages()->getNested($key);
        }

        if ($rawMessage === null) {
            return $key;
        }

        foreach ($replaces as $replace => $value) {
            $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
        }

        return $rawMessage;
    }
}