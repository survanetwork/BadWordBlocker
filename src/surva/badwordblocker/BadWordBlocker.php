<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 06.10.16
 * Time: 18:05
 */

namespace surva\badwordblocker;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class BadWordBlocker extends PluginBase {
    /* @var Config */
    private $messages;

    /* @var array */
    private $blockedWords;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->messages = new Config($this->getFile() . "resources/languages/" . $this->getConfig()->get("language") . ".yml");

        $this->blockedWords = $this->getConfig()->get("badwords");

        if(!is_array($this->blockedWords)) {
            $this->blockedWords = explode(',', $this->blockedWords);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch(strtolower($command->getName())) {
            case "chat":
                if(isset($sender->chatDisabled)) {
                    unset($sender->chatDisabled);

                    $sender->sendMessage($this->getMessage("chat.on"));
                } else {
                    $sender->chatDisabled = true;

                    $sender->sendMessage($this->getMessage("chat.off"));
                }

                return true;
        }

        return false;
    }

    /**
     * Check the message of a player on the different aspects (true = alright; false = found something)
     *
     * @param Player $player
     * @param string $message
     * @return bool
     */
    public function checkMessage(Player $player, string $message): bool {
        if($this->contains($message, $this->getBlockedWords())) {
            $player->sendMessage($this->getMessage("blocked.message"));

            return false;
        }

        if(isset($player->lastWritten)) {
            if($player->lastWritten == $message) {
                $player->sendMessage($this->getMessage("blocked.lastwritten"));

                return false;
            }
        }

        if(isset($player->timeWritten)) {
            if($player->timeWritten > new \DateTime()) {
                $player->sendMessage($this->getMessage("blocked.timewritten"));

                return false;
            }
        }

        $uppercasePercentage = $this->getConfig()->get("uppercasepercentage");
        $minimumChars = $this->getConfig()->get("minimumchars");

        $len = strlen($message);

        if($len > 0) {
            if(($this->countUppercaseChars($message) / $len) >= $uppercasePercentage AND $len >= $minimumChars) {
                $player->sendMessage($this->getMessage("blocked.caps"));

                return false;
            }
        }

        $player->timeWritten = new \DateTime();
        $player->timeWritten = $player->timeWritten->add(new \DateInterval("PT" . $this->getConfig()->get("waitingtime") . "S"));
        $player->lastWritten = $message;

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
     * @param $string
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
