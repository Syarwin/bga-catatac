<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Schrodinger extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('Condition: opponent owns the ball / you own the ball'),
      clienttranslate('Effects: counter a storage attempt / attempt storage from neutral street')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return $player->isOwningTheBall() ?
      [
        'action' => STORAGE_ATTEMPT,
        'args' => ['n' => 2]
      ] :
      [
        'action' => COUNTER_STORAGE
      ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }

  public function canCounterStorage(): bool
  {
    return true;
  }
}
