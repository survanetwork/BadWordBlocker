<?php

/**
 * BadWordBlocker | plugin main class
 */

namespace surva\badwordblocker;

use DateInterval;
use DateTime;
use DirectoryIterator;
use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\badwordblocker\form\ImportSelectForm;
use surva\badwordblocker\util\Messages;

class BadWordBlocker extends PluginBase
{
    /**
     * @var \pocketmine\utils\Config default language config
     */
    private Config $defaultMessages;

    /**
     * @var array available language configs
     */
    private array $translationMessages;

    /**
     * @var array available sources for lists to import
     */
    private array $availableListSources;

    /**
     * @var array players to which the bypassed message was already sent during this runtime
     */
    private array $bypassedMessageSent;

    private array $blockedWords;
    private array $playersTimeWritten;
    private array $playersLastWritten;
    private array $playersViolations;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->defaultMessages = new Config($this->getFile() . "resources/languages/en.yml");
        $this->loadLanguageFiles();

        $listSourcesConfig = new Config($this->getFile() . "resources/list_sources.yml");
        $this->availableListSources = $listSourcesConfig->getNested("sources");

        $this->loadConfig();

        $this->bypassedMessageSent = [];
        $this->playersTimeWritten = [];
        $this->playersLastWritten = [];
        $this->playersViolations  = [];

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    /**
     * Listen for plugin command
     *
     * @param  \pocketmine\command\CommandSender  $sender
     * @param  \pocketmine\command\Command  $command
     * @param  string  $label
     * @param  array  $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (count($args) < 1) {
            return false;
        }

        if ($args[0] === "import") {
            if (!($sender instanceof Player)) {
                return false;
            }

            $messages = new Messages($this, $sender);
            $sender->sendForm(new ImportSelectForm($this, $messages, $this->availableListSources));

            return true;
        }

        return false;
    }

    /**
     * Check the message of a player on the different aspects (true = alright; false = found something)
     *
     * @param  \pocketmine\player\Player  $player
     * @param  string  $message
     *
     * @return bool
     */
    public function checkMessage(Player $player, string $message): bool
    {
        $playerName = $player->getName();

        if ($this->getConfig()->get("ignorespaces", true) === true) {
            $message = str_replace(" ", "", $message);
        }

        $translMessages = new Messages($this, $player);

        if (($blocked = $this->contains($message, $this->blockedWords)) !== null) {
            if ($player->hasPermission("badwordblocker.bypass.swear")) {
                $this->sendBypassedMessage($player);

                return true;
            }

            if ($this->getConfig()->get("showblocked", false) === true) {
                $player->sendMessage(
                    $translMessages->getMessage("blocked.messagewithblocked", ["blocked" => $blocked])
                );
            } else {
                $player->sendMessage($translMessages->getMessage("blocked.message"));
            }

            $this->handleViolation($player);

            return false;
        }

        if (isset($this->playersLastWritten[$playerName])) {
            if ($this->playersLastWritten[$playerName] === $message) {
                if ($player->hasPermission("badwordblocker.bypass.same")) {
                    $this->sendBypassedMessage($player);

                    return true;
                }

                $player->sendMessage($translMessages->getMessage("blocked.lastwritten"));
                $this->handleViolation($player);

                return false;
            }
        }

        if (isset($this->playersTimeWritten[$playerName])) {
            if ($this->playersTimeWritten[$playerName] > new DateTime()) {
                if ($player->hasPermission("badwordblocker.bypass.spam")) {
                    $this->sendBypassedMessage($player);

                    return true;
                }

                $player->sendMessage($translMessages->getMessage("blocked.timewritten"));
                $this->handleViolation($player);

                return false;
            }
        }

        $uppercasePercentage = $this->getConfig()->get("uppercasepercentage", 0.75);
        $minimumChars        = $this->getConfig()->get("minimumchars", 3);

        $messageLength = strlen($message);

        if (
                $messageLength > $minimumChars and ($this->countUppercaseChars(
                    $message
                ) / $messageLength) >= $uppercasePercentage
        ) {
            if ($player->hasPermission("badwordblocker.bypass.caps")) {
                $this->sendBypassedMessage($player);

                return true;
            }

            $player->sendMessage($translMessages->getMessage("blocked.caps"));
            $this->handleViolation($player);

            return false;
        }

        try {
            $this->playersTimeWritten[$playerName] = new DateTime();
            $this->playersTimeWritten[$playerName] = $this->playersTimeWritten[$playerName]->add(
                new DateInterval("PT" . $this->getConfig()->get("waitingtime", 2) . "S")
            );
            $this->playersLastWritten[$playerName] = $message;
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Handle the occurrence of a chat block event, e.g. kick or ban the player if configured
     *
     * @param  \pocketmine\player\Player  $player
     */
    private function handleViolation(Player $player): void
    {
        $playerName = $player->getName();

        if (!isset($this->playersViolations[$playerName])) {
            $this->playersViolations[$playerName] = 0;
        }

        $this->playersViolations[$playerName]++;

        $violKick       = $this->getConfig()->getNested("violations.kick", 0);
        $violBan        = $this->getConfig()->getNested("violations.ban", 0);
        $resetAfterKick = $this->getConfig()->getNested("violations.resetafterkick", true);

        $translMessages = new Messages($this, $player);

        if ($this->playersViolations[$playerName] === $violKick) {
            $player->kick($translMessages->getMessage("punishment.kick"));

            if ($resetAfterKick) {
                $this->playersViolations[$playerName] = 0;
            }
        } elseif ($this->playersViolations[$playerName] === $violBan) {
            $this->getServer()->getNameBans()->addBan($playerName, $translMessages->getMessage("punishment.ban"));
            $player->kick($translMessages->getMessage("punishment.ban"));

            $this->playersViolations[$playerName] = 0;
        }
    }

    /**
     * Send filter bypassed reminder to a player if not already sent during this session
     *
     * @param  \pocketmine\player\Player  $pl
     *
     * @return void
     */
    private function sendBypassedMessage(Player $pl): void
    {
        if ($this->getConfig()->get("send_bypassed_message", true) !== true) {
            return;
        }

        $id = $pl->getId();

        if (isset($this->bypassedMessageSent[$id])) {
            return;
        }

        $this->sendMessage($pl, "filter_bypassed");
        $this->bypassedMessageSent[$id] = true;
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
        $ignoreSpaces = $this->getConfig()->get("ignorespaces", true) === true;

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
     * Counts uppercase chars in a string
     *
     * @param  string  $string
     *
     * @return int
     */
    private function countUppercaseChars(string $string): int
    {
        preg_match_all("/[A-Z]/", $string, $matches);

        return count($matches[0]);
    }

    /**
     * Load bad word list from config file
     *
     * @return void
     */
    public function loadConfig(): void
    {
        $this->blockedWords = $this->getConfig()->get("badwords", ["fuck", "shit", "bitch"]);
    }

    /**
     * Shorthand to send a translated message to a command sender
     *
     * @param  \pocketmine\command\CommandSender  $sender
     * @param  string  $key
     * @param  array  $replaces
     *
     * @return void
     */
    public function sendMessage(CommandSender $sender, string $key, array $replaces = []): void
    {
        $messages = new Messages($this, $sender);

        $sender->sendMessage($messages->getMessage($key, $replaces));
    }

    /**
     * Load all available language files
     *
     * @return void
     */
    private function loadLanguageFiles(): void
    {
        $languageFilesDir = $this->getFile() . "resources/languages/";

        foreach (new DirectoryIterator($languageFilesDir) as $dirObj) {
            if (!($dirObj instanceof DirectoryIterator)) {
                continue;
            }

            if (!$dirObj->isFile() || !str_ends_with($dirObj->getFilename(), ".yml")) {
                continue;
            }

            preg_match("/^[a-z][a-z]/", $dirObj->getFilename(), $fileNameRes);

            if (!isset($fileNameRes[0])) {
                continue;
            }

            $langId = $fileNameRes[0];

            $this->translationMessages[$langId] = new Config(
                $this->getFile() . "resources/languages/" . $langId . ".yml"
            );
        }
    }

    /**
     * @return array
     */
    public function getTranslationMessages(): array
    {
        return $this->translationMessages;
    }

    /**
     * @return \pocketmine\utils\Config
     */
    public function getDefaultMessages(): Config
    {
        return $this->defaultMessages;
    }
}
