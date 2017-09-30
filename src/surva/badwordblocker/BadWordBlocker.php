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

class BadWordBlocker extends PluginBase {
    /* @var array */
    private $list;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->list = $this->getConfig()->get("badwords");
        $this->list = explode(',', $this->list);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch(strtolower($command->getName())) {
            case "chat":
                if(isset($sender->nochat)) {
                    unset($sender->nochat);

                    $sender->sendMessage($this->getConfig()->get("chaton"));
                } else {
                    $sender->nochat = true;

                    $sender->sendMessage($this->getConfig()->get("chatoff"));
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
        if($this->contains($message, $this->getList())) {
            $player->sendMessage($this->getConfig()->get("blockmessage"));

            return false;
        }

        if(isset($player->lastwritten)) {
            if($player->lastwritten == $message) {
                $player->sendMessage($this->getConfig()->get("lastwritten"));

                return false;
            }
        }

        if(isset($player->timewritten)) {
            if($player->timewritten > new \DateTime()) {
                $player->sendMessage($this->getConfig()->get("timewritten"));

                return false;
            }
        }

        if(
            ($this->countUppercaseChars($message) / strlen($message)) >= $this->getConfig()->get("uppercasepercentage")
            && strlen($message) >= $this->getConfig()->get("minimumchars")
        ) {
            $player->sendMessage($this->getConfig()->get("caps"));

            return false;
        }

        $player->timewritten = new \DateTime();
        $player->timewritten = $player->timewritten->add(new \DateInterval("PT" . $this->getConfig()->get("waitingtime") . "S"));
        $player->lastwritten = $message;

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
     * @return array
     */
    public function getList(): array {
        return $this->list;
    }
}