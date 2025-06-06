<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Bingo extends PawnCard
{
  public function getActionBloc(): array
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        ['action' => MOVE_TOKEN],
        ['action' => STORAGE_ATTEMPT],
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
