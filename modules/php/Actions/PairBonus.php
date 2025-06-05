<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
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

  public function actPairBonus()
  {
    $this->insertAsChild([
      'type' => NODE_XOR,
      'childs' => [
        ['action' => STEAL_TOKEN],
        ['action' => MOVE_TOKEN],
        ['action' => STORAGE_ATTEMPT],
      ],
    ]);
  }
}
