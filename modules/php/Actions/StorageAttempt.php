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
    return $ball->isOwned($player) && $ball->getLocation() == $player->getStreetLocation();
  }

  public function stStorageAttempt()
  {
    return [];
  }

  public function actStorageAttempt()
  {
    die("test");
  }
}
