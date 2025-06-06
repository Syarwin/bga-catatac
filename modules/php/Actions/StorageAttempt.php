<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class StorageAttempt extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_GENERIC_AUTOMATIC;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function getDescription(): string|array
  {
    return clienttranslate("Attempt storage");
  }

  public function isDoable(Player $player): bool
  {
    $ball = Meeples::getBall();
    return $ball->isOwned($player) && $this->getNewLocation($player) == $player->getHideoutLocation();
  }

  public function stStorageAttempt()
  {
    return [];
  }

  public function getN()
  {
    return $this->getCtxArg('n') ?? 1;
  }

  public function getNewLocation(Player $player)
  {
    $ball = Meeples::getBall();
    return $ball->getLocation() + ($player->getTeam() == WHITE_SIDE ? -1 : 1) * $this->getN();
  }

  public function actStorageAttempt()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $ball->setLocation($this->getNewLocation($player));
    Notifications::storageAttempt($player, $ball);
  }
}
