<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Duo extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('Condition: none'),
      clienttranslate('Effects: play again')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => REPLAY
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
