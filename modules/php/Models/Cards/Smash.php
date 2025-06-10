<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Smash extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** make a storage attempt')
    ];
  }

  public function getActionBloc(): array
  {
    return [
      'action' => STORAGE_ATTEMPT,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
