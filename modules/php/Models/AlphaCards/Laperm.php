<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class Laperm extends AlphaCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("LaPerm");
    $this->tooltip = [
      clienttranslate('**Condition:** No condition.'),
      clienttranslate('**Effects:** Steal the yarn ball / Move the yarn ball / Make a hoarding attempt.')
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
