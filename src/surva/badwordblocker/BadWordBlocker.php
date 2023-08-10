<?php

/**
 * BadWordBlocker | plugin main class
 */

namespace surva\badwordblocker;

use DirectoryIterator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\badwordblocker\filter\FilterManager;
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
     * @var \surva\badwordblocker\filter\FilterManager class for managing registered filters and check messages
     */
    private FilterManager $filterManager;

    /**
     * @var array available sources for lists to import
     */
    private array $availableListSources;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->defaultMessages = new Config($this->getFile() . "resources/languages/en.yml");
        $this->loadLanguageFiles();

        $this->filterManager = new FilterManager($this);

        $listSourcesConfig = new Config($this->getFile() . "resources/list_sources.yml");
        $this->availableListSources = $listSourcesConfig->getNested("sources");

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
     * @return \surva\badwordblocker\filter\FilterManager
     */
    public function getFilterManager(): FilterManager
    {
        return $this->filterManager;
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
