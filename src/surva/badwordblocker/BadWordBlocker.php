<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 06.10.16
 * Time: 18:05
 */

namespace surva\badwordblocker;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class BadWordBlocker extends PluginBase {
    /* @var Config */
    private $messages;

    /* @var array */
    private $blockedWords;

    /* @var array */
    private $playersTimeWritten;

    /* @var array */
    private $playersLastWritten;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->messages = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->blockedWords = $this->getConfig()->get("badwords", array("fuck", "shit", "bitch"));

        $this->playersTimeWritten = array();
        $this->playersLastWritten = array();
    }

    /**
     * Check the message of a player on the different aspects (true = alright; false = found something)
     *
     * @param Player $player
     * @param string $message
     * @return bool
     */
    public function checkMessage(Player $player, string $message): bool {
        $playerName = $player->getName();

        if($this->getConfig()->get("ignorespaces", true) === true) {
            $message = str_replace(" ", "", $message);
        }

        if($this->contains($message, $this->getBlockedWords())) {
            $player->sendMessage($this->getMessage("blocked.message"));

            return false;
        }

        if(isset($this->playersLastWritten[$playerName])) {
            if($this->playersLastWritten[$playerName] === $message) {
                $player->sendMessage($this->getMessage("blocked.lastwritten"));

                return false;
            }
        }

        if(isset($this->playersTimeWritten[$playerName])) {
            if($this->playersTimeWritten[$playerName] > new \DateTime()) {
                $player->sendMessage($this->getMessage("blocked.timewritten"));

                return false;
            }
        }

        $uppercasePercentage = $this->getConfig()->get("uppercasepercentage", 0.75);
        $minimumChars = $this->getConfig()->get("minimumchars", 3);

        $messageLength = strlen($message);

        if($messageLength > $minimumChars AND ($this->countUppercaseChars(
                    $message
                ) / $messageLength) >= $uppercasePercentage) {
            $player->sendMessage($this->getMessage("blocked.caps"));

            return false;
        }

        try {
            $this->playersTimeWritten[$playerName] = new \DateTime();
            $this->playersTimeWritten[$playerName] = $this->playersTimeWritten[$playerName]->add(
                new \DateInterval("PT" . $this->getConfig()->get("waitingtime", 2) . "S")
            );
            $this->playersLastWritten[$playerName] = $message;
        } catch(\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Check if a string contains a specific string from an array
     *
     * @param $string
     * @param array $contains
     * @return bool
     */
    public function contains($string, array $contains): bool {
        foreach($contains as $contain) {
            if(strpos(strtolower($string), $contain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Counts uppercase chars in a string
     *
     * @param string $string
     * @return int
     */
    public function countUppercaseChars(string $string): int {
        preg_match_all("/[A-Z]/", $string, $matches);

        return count($matches[0]);
    }

    /**
     * Get a translated message
     *
     * @param string $key
     * @param array $replaces
     * @return string
     */
    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->getMessages()->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }

            return $rawMessage;
        }

        return $key;
    }

    /**
     * @return array
     */
    public function getBlockedWords(): array {
        return $this->blockedWords;
    }

    /**
     * @return Config
     */
    public function getMessages(): Config {
        return $this->messages;
    }
}
