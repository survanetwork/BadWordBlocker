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
     * @return array
     */
    public function getList(): array {
        return $this->list;
    }
}