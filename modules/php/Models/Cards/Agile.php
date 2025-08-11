<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Agile extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** opponent owns the ball'),
      clienttranslate('**Effect:** steal the ball, move the ball and replay')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'childs' => [
        [
          'action' => STEAL_TOKEN,
        ],
        [
          'action' => MOVE_TOKEN,
        ],
        [
          'action' => REPLAY
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }
}
