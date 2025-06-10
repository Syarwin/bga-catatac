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
      clienttranslate('**Condition:** opponent owns the ball'),
      clienttranslate('**Effect:** counter the storage attempt')
    ];
  }

  public function getActionBloc(): array
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
