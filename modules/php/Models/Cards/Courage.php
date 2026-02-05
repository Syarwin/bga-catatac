<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Courage extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** Opponent owns the yarn ball.'),
      clienttranslate('**Effect:** Move the yarn ball.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => MOVE_TOKEN,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }
}
