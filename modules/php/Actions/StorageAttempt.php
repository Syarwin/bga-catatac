<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Globals;
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
    if (!$ball->isOwned($player)) return false;

    $loc = $ball->getLocation();
    $dir = ($player->getTeam() == WHITE_SIDE ? -1 : 1);
    $target = $player->getHideoutLocation();

    $n = $this->getN();
    foreach ($n as $m) {
      if (($loc + $dir * $m) == $target) {
        return true;
      }
    }

    return false;
  }

  public function stStorageAttempt()
  {
    return [];
  }

  public function getN(): array
  {
    $n = $this->getCtxArg('n') ?? 1;
    return is_array($n) ? $n : [$n];
  }

  public function actStorageAttempt()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $ball->setLocation($player->getHideoutLocation());
    Notifications::storageAttempt($player, $ball);

    // Prevent skipping next player
    Globals::setDistracted(false);
  }
}
