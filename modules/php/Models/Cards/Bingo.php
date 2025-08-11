<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Bingo extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** either move the ball or make a storage attempt')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        ['action' => MOVE_TOKEN],
        ['action' => STORAGE_ATTEMPT],
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
