<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Dribble extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** move the ball and skip next player\'s turn')
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
          'action' => DISTRACTION,
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
