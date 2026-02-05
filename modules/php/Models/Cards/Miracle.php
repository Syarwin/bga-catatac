<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Miracle extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** Opponent owns the yarn ball.'),
      clienttranslate('**Effect:** Counter the hoarding attempt.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => COUNTER_STORAGE,
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall();
  }

  public function canCounterStorage(): bool
  {
    return true;
  }

  public function canBePlayedCoop(): bool
  {
    return true;
  }
}
