<?php

/**
 * BadWordBlocker | writing speed (spam) filter, adds
 * a minimum delay before the next message can be sent
 */

namespace surva\badwordblocker\filter;

use DateInterval;
use DateTime;
use Exception;
use pocketmine\player\Player;
use surva\badwordblocker\util\Messages;

class SpeedFilter extends Filter
{
    private FilterManager $filterManager;

    /**
     * @var DateTime[] times of players when they wrote the last message
     */
    private array $playersTimeWritten;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
        $this->playersTimeWritten = [];
    }

    /**
     * @inheritDoc
     */
    public function check(Player $pl, string $message): bool
    {
        $plName = $pl->getName();

        return isset($this->playersTimeWritten[$plName]) && $this->playersTimeWritten[$plName] > new DateTime();
    }

    /**
     * @inheritDoc
     */
    public function action(Player $pl, string $originalMessage): void
    {
        $messages = new Messages($this->filterManager->getBadWordBlocker(), $pl);

        $pl->sendMessage($messages->getMessage("blocked.timewritten"));
        $this->filterManager->handleViolation($pl);
    }

    /**
     * @inheritDoc
     */
    public function saveMessage(Player $pl, string $message): void
    {
        $plName = $pl->getName();

        try {
            $this->playersTimeWritten[$plName] = new DateTime();
            $this->playersTimeWritten[$plName] = $this->playersTimeWritten[$plName]->add(
                new DateInterval(
                    "PT" . $this->filterManager->getBadWordBlocker()->getConfig()->get(
                        "waitingtime",
                        2
                    ) . "S"
                )
            );
        } catch (Exception $e) {
            // do nothing
        }
    }

    /**
     * @inheritDoc
     */
    public function getBypassPermission(): string
    {
        return "badwordblocker.bypass.spam";
    }
}
