<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class MaineCoon extends AlphaCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("Maine Coon");
    $this->tooltip = [
      clienttranslate('Condition: no condition'),
      clienttranslate('Effects: steal the ball / move the ball / hoarding attempt')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        [
          'action' => STEAL_TOKEN,
        ],
        [
          'action' => MOVE_TOKEN
        ],
        [
          'action' => STORAGE_ATTEMPT,
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
