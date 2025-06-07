<?php

/**
 * BadWordBlocker | form for confirming list import
 */

namespace surva\badwordblocker\form;

use JsonException;
use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\utils\InternetException;
use surva\badwordblocker\BadWordBlocker;
use surva\badwordblocker\util\Import;
use surva\badwordblocker\util\Messages;

class ImportConfirmForm implements Form
{
    private BadWordBlocker $badWordBlocker;

    /**
     * @var string[] properties of the selected list source
     */
    private array $selectedListSource;

    private string $type = "custom_form";
    private string $title;

    /**
     * @var mixed[]
     */
    private array $content;

    /**
     * @param  \surva\badwordblocker\BadWordBlocker  $badWordBlocker
     * @param  \surva\badwordblocker\util\Messages  $messages
     * @param  string[]  $selectedListSource
     */
    public function __construct(BadWordBlocker $badWordBlocker, Messages $messages, array $selectedListSource)
    {
        $this->badWordBlocker = $badWordBlocker;
        $this->selectedListSource = $selectedListSource;

        $this->title = $messages->getMessage("import.form.confirm.title");
        $this->content = [
          [
            "type" => "label",
            "text" => $messages->getMessage("import.form.confirm.description", ["name" => $selectedListSource["name"]])
          ],
          [
            "type" => "label",
            "text" => $messages->getMessage(
                "import.form.confirm.license",
                [
                    "license" => $selectedListSource["license"],
                    "credits_url" => $selectedListSource["credits_url"]
                ]
            )
          ],
        ];
    }

    /**
     * Handle form submit
     *
     * @param  \pocketmine\player\Player  $player
     * @param $data
     *
     * @return void
     */
    public function handleResponse(Player $player, $data): void
    {
        if (!is_array($data)) {
            return;
        }

        try {
            $arr = Import::textListAsArray($this->selectedListSource["source_url"]);
        } catch (InternetException $e) {
            $this->badWordBlocker->sendMessage($player, "import.error_code.web_request");

            return;
        }
        try {
            Import::importArrayToConfig($this->badWordBlocker->getConfig(), $arr);
        } catch (JsonException $e) {
            $this->badWordBlocker->sendMessage($player, "import.error_code.config_save");

            return;
        }
        $this->badWordBlocker->getFilterManager()->reloadFilterConfigs();

        $this->badWordBlocker->sendMessage($player, "import.success", ["name" => $this->selectedListSource["name"]]);
    }

    /**
     * Return JSON data of the form
     *
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return [
          "type"    => $this->type,
          "title"   => $this->title,
          "content" => $this->content,
        ];
    }
}
