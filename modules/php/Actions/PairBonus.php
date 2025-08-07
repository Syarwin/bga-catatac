<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class PairBonus extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_GENERIC_AUTOMATIC;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function getDescription(): string|array
  {
    return clienttranslate("Pair Bonus");
  }

  public function stPairBonus()
  {
    return [];
  }

  public function isDoable(Player $player): bool
  {
    $ball = Meeples::getBall();
    return !in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);
  }

  public function isOptional(): bool
  {
    return !$this->isDoable($this->getPlayer());
  }

  public function actPairBonus()
  {
    $actions = [
      ['action' => STEAL_TOKEN],
      ['action' => MOVE_TOKEN, 'args' => ['mustOwn' => true]],
      ['action' => STORAGE_ATTEMPT],
    ];

    $player = Players::getActive();
    $childs = [];
    foreach ($actions as $action) {
      if ($player->canTakeAction($action['action'], $action)) {
        $childs[] = $action;
      }
    }

    $this->insertAsChild([
      'type' => NODE_XOR,
      'childs' => $childs,
    ]);
  }
}
