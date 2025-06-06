<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Sprint extends PawnCard
{
  public function getActionBloc(): array
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        ['action' => MOVE_TOKEN],
        ['action' => MOVE_TOKEN, 'args' => ['n' => 2]],
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
