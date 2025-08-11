<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Synchro extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('Condition: opponent owns the ball'),
      clienttranslate('Effects: counter a storage attempt and play again')
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
          'action' => REPLAY
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
