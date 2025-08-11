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
      clienttranslate('**Condition:** opponent owns the ball'),
      clienttranslate('**Effect:** steal the ball and skip next player\'s turn')
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
