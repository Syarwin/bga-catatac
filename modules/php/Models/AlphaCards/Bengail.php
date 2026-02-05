<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class Bengail extends AlphaCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("Bengal");
    $this->tooltip = [
      clienttranslate('**Condition:** You own the yarn ball.'),
      clienttranslate('**Effects:** Move the yarn ball and skip next player\'s turn.')
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
          'action' => DISTRACTION
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
