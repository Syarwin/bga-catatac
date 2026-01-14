<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Reflexes extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** you own the ball'),
      clienttranslate('**Effect:** hoarding attempt from 1 or 2 steps')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return ['action' => STORAGE_ATTEMPT, 'args' => ['n' => [1, 2]]];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return $player->isOwningTheBall();
  }
}
