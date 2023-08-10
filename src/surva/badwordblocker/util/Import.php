<?php

/**
 * BadWordBlocker | utility for importing lists to config
 */

namespace surva\badwordblocker\util;

use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;

class Import
{
    private const HTTP_OK = 200;

    /**
     * Get a simple plain text list from the internet as an array
     *
     * @param  string  $sourceUrl
     *
     * @return array
     */
    public static function textListAsArray(string $sourceUrl): array
    {
        $webRes = Internet::getURL($sourceUrl);

        if ($webRes->getCode() !== self::HTTP_OK) {
            throw new InternetException();
        }

        $text = $webRes->getBody();
        $words = explode(PHP_EOL, $text);
        return array_filter($words, function ($word) {
            return $word !== "";
        });
    }

    /**
     * Import array of blocked words to config
     *
     * @param  \pocketmine\utils\Config  $config
     * @param  array  $list
     *
     * @return void
     * @throws \JsonException
     */
    public static function importArrayToConfig(Config $config, array $list): void
    {
        $config->set("badwords", $list);
        $config->save();
    }
}
