<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class White extends PointCard
{
  public static function getPoints(Player $player): int
  {
    return $player->getTeam() == WHITE_SIDE ? 2 : 1;
  }
}
