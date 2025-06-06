<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Capture extends PawnCard
{
  public function getActionBloc(): array
  {
    return [
      'action' => STEAL_TOKEN,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }
}
