<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Impact extends PawnCard
{
  public function getActionBloc(): array
  {
    return [
      'action' => STORAGE_ATTEMPT,
      'args' => ['n' => 2],
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
