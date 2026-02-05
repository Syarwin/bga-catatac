<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Beurgh extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** Opponent owns the yarn ball.'),
      clienttranslate('**Effect:** Steal the yarn ball and skip next player\'s turn.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => STEAL_TOKEN],
        ['action' => DISTRACTION]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }
}
