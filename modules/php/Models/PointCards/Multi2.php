<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class Multi2 extends PointCard
{
  public static function getPoints(Player $player): int
  {
    return 2;
  }
}
