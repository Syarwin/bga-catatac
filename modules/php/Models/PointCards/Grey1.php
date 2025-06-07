<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class Grey1 extends PointCard
{
  public static function getPoints(Player $player): int
  {
    return 1;
  }
}
