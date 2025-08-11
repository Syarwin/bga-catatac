<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Sprint extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** move the ball by 1 or 2 steps')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        ['action' => MOVE_TOKEN],
        ['action' => MOVE_TOKEN, 'args' => ['n' => 2]],
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
