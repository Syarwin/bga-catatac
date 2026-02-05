<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class Angora extends AlphaCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("Angora");
    $this->tooltip = [
      clienttranslate('**Condition:** Opponent owns the yarn ball.'),
      clienttranslate('**Effects:** Counter a hoarding attempt and steal the yarn ball.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'childs' => [
        [
          'action' => COUNTER_STORAGE,
        ],
        [
          'action' => STEAL_TOKEN,
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall() && in_array(Meeples::getBall()->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);
  }

  public function canCounterStorage(): bool
  {
    return true;
  }
}
