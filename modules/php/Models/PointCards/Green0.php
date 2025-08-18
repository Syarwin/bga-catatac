<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class Green0 extends PointCard
{
  protected int $sardines = 1;

  public static function getPoints(Player $player): int
  {
    return 0;
  }
}
