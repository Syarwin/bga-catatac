<?php

namespace Bga\Games\Catatac\Models;

class PointCard extends Card
{
  public static function getPoints(Player $player): int
  {
    return 0;
  }
}
