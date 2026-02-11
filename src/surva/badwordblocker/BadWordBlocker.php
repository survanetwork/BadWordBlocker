<?php

/**
 * BadWordBlocker | plugin main class, initialize filter manager and list
 * sources
 */

namespace surva\badwordblocker;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\badwordblocker\filter\FilterManager;
use surva\badwordblocker\form\ImportSelectForm;
use surva\badwordblocker\util\Messages;
use Symfony\Component\Filesystem\Path;

class BadWordBlocker extends PluginBase
{
    /**
     * @var Config default language config
     */
    private Config $defaultMessages;
    /**
     * @var Config[] available language configs
     */
    private array $translationMessages;
    /**
     * @var FilterManager class for managing registered filters and check messages
     */
    private FilterManager $filterManager;
    /**
     * @var array<string, string>[] available sources for lists to import
     */
    private array $availableListSources;

    /**
     * Initial setup, load language files and list sources, initialize
     * filter manager and event listener
     *
     * @return void
     */
    public function onEnable(): void
    {
        $this->defaultMessages = new Config($this->getResourcePath(Path::join("languages", "en.yml")));
        $this->loadLanguageFiles();

        $this->filterManager = new FilterManager($this);

        $listSourcesConfig = new Config($this->getResourcePath("list_sources.yml"));
        $this->availableListSources = $listSourcesConfig->getNested("sources");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

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
     * @param CommandSender $sender
     * @param string $key
     * @param array<string, string> $replaces
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
        $resources = $this->getResources();
        $this->translationMessages = [];

        foreach ($resources as $resource) {
            $normalizedPath = Path::normalize($resource->getPathname());
            if (!preg_match("/languages\/[a-z]{2}.yml$/", $normalizedPath)) {
                continue;
            }

            preg_match("/^[a-z][a-z]/", $resource->getFilename(), $fileNameRes);

            if (!isset($fileNameRes[0])) {
                continue;
            }

            $langId = $fileNameRes[0];

            $this->translationMessages[$langId] = new Config(
                $this->getResourcePath(Path::join("languages", $langId . ".yml"))
            );
        }
    }

    /**
     * @return FilterManager
     */
    public function getFilterManager(): FilterManager
    {
        return $this->filterManager;
    }

    /**
     * @return Config[]
     */
    public function getTranslationMessages(): array
    {
        return $this->translationMessages;
    }

    /**
     * @return Config
     */
    public function getDefaultMessages(): Config
    {
        return $this->defaultMessages;
    }
}
