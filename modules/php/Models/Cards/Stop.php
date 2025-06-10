<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Stop extends PawnCard
{
  public function getActionBloc(): array
  {
    return [
      'childs' => [
        [
          'action' => COUNTER_STORAGE,
        ],
        [
          'action' => TOSS_TOKEN
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }

  public function canCounterStorage(): bool
  {
    return true;
  }
}
