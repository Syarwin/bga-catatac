<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Charge extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** move the ball')
    ];
  }

  public function getActionBloc(): array
  {
    return [
      'action' => MOVE_TOKEN,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
