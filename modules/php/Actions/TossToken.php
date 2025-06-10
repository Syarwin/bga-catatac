<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class TossToken extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_GENERIC_AUTOMATIC;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function isIrreversible(?Player $player = null): bool
  {
    return true;
  }

  public function getDescription(): string|array
  {
    return clienttranslate("Flip the ball");
  }

  public function stTossToken()
  {
    return [];
  }

  public function actTossToken()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $ball->setState(bga_rand(0, 1));
    Notifications::tossBall($player, $ball);
    return true;
  }
}
