<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class Ragdoll extends AlphaCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("Ragdoll");
    $this->tooltip = [
      clienttranslate('Condition: no condition'),
      clienttranslate('Effects: move the ball, randomly flip the ball and replay')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'childs' => [
        [
          'action' => MOVE_TOKEN,
        ],
        [
          'action' => TOSS_TOKEN
        ],
        [
          'action' => REPLAY
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
