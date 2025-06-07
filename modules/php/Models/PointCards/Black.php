<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class Black extends PointCard
{
  public static function getPoints(Player $player): int
  {
    return $player->getTeam() == BLACK_SIDE ? 2 : 1;
  }
}
