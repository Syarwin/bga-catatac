<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Miracle extends PawnCard
{
  public function getActionBloc(): array
  {
    return [];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }
}
