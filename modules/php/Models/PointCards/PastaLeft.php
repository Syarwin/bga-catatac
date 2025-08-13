<?php

namespace Bga\Games\Catatac\Models\PointCards;

use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\PointCard;

class PastaLeft extends PointCard
{
  public static function getPoints(Player $player): int
  {
    $team = $player->getTeam();
    $cards = Cards::getInLocation("points-$team");
    foreach ($cards as $card) {
      if ($card->getType() == 'Points-Pasta-Right') {
        return 3;
      }
    }

    return 1;
  }
}
