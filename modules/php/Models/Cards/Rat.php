<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Rat extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** none'),
      clienttranslate('**Effect:** skip next player\'s turn')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => DISTRACTION,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
