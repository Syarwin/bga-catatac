<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Stop extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('Condition: opponent owns the ball'),
      clienttranslate('Effects: counter a storage attempt and randomly flip the ball')
    ];
  }

  public function getActionBloc(): array
  {
    return [
      'childs' => [
        [
          'action' => COUNTER_STORAGE,
        ],
        [
          'action' => TOSS_TOKEN
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }

  public function canCounterStorage(): bool
  {
    return true;
  }
}
