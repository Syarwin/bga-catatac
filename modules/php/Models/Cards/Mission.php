<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Mission extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** none'),
      clienttranslate('**Effect:** randomly flip the ball and move it')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'childs' => [
        [
          'action' => TOSS_TOKEN,
        ],
        [
          'action' => MOVE_TOKEN
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
