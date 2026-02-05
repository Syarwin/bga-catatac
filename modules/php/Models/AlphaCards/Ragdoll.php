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
      clienttranslate('**Condition:** No condition.'),
      clienttranslate('**Effects:** move the yarn ball, randomly toss the yarn ball (heads or tails) and replay.')
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
