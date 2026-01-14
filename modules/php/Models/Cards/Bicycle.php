<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Bicycle extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('Condition: opponent owns the ball'),
      clienttranslate('Effects: counter a hoarding attempt and skip next player\'s turn')
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
          'action' => DISTRACTION
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
