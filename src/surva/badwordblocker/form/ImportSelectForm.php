<?php

/**
 * BadWordBlocker | form for selecting list to import
 */

namespace surva\badwordblocker\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use surva\badwordblocker\BadWordBlocker;
use surva\badwordblocker\util\Messages;

class ImportSelectForm implements Form
{
    private BadWordBlocker $badWordBlocker;
    private Messages $messages;
    /**
     * @var string[][] properties of available list sources
     */
    private array $listSources;

    private string $type = "custom_form";
    private string $title;
    /**
     * @var array<string, mixed>[]
     */
    private array $content;

    /**
     * @param BadWordBlocker $badWordBlocker
     * @param Messages $messages
     * @param string[][] $listSources
     */
    public function __construct(BadWordBlocker $badWordBlocker, Messages $messages, array $listSources)
    {
        $this->badWordBlocker = $badWordBlocker;
        $this->messages = $messages;
        $this->listSources = $listSources;

        $this->title = $messages->getMessage("import.form.select.title");
        $this->content = [
          [
            "type" => "label",
            "text" => $messages->getMessage("import.form.select.description"),
          ],
          [
            "type" => "dropdown",
            "text" => $messages->getMessage("import.form.select.dropdown"),
            "options" => array_map(function ($listSource) {
                return $listSource["name"];
            }, $listSources),
          ],
        ];
    }

    /**
     * Handle form submit, check if selected list index is valid
     * and open import confirm form
     *
     * @param Player $player
     * @param $data
     *
     * @return void
     */
    public function handleResponse(Player $player, $data): void
    {
        if (!is_array($data)) {
            return;
        }

        if (count($data) < 2) {
            return;
        }

        $selectedListI = $data[1];

        if (!isset($this->listSources[$selectedListI])) {
            return;
        }

        $selectedList = $this->listSources[$selectedListI];
        $player->sendForm(new ImportConfirmForm($this->badWordBlocker, $this->messages, $selectedList));
    }

    /**
     * Return JSON data of the form
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
          "type" => $this->type,
          "title" => $this->title,
          "content" => $this->content,
        ];
    }
}
