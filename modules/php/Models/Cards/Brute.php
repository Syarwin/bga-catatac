<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Brute extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** You own the yarn ball.'),
      clienttranslate('**Effect:** Make a hoarding attempt from 3 alleys.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => STORAGE_ATTEMPT,
      'args' => ['n' => 3],
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
