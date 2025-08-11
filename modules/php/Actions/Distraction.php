<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\Player;

class Distraction extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_GENERIC_AUTOMATIC;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function isDoable(Player $player): bool
  {
    $ball = Meeples::getBall();
    return !in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);
  }

  public function isOptional(): bool
  {
    return !$this->isDoable($this->getPlayer());
  }

  public function stDistraction()
  {
    return [];
  }

  public function actDistraction()
  {
    Globals::setDistracted(true);
    Notifications::message(clienttranslate('Distraction: next player\'s turn will be skipped!'));
  }
}
