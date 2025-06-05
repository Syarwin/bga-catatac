<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class StealToken extends \Bga\Games\Catatac\Models\Action
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
    return clienttranslate("Steal the ball");
  }

  public function isDoable(Player $player): bool
  {
    return !Meeples::getBall()->isOwned($player);
  }

  public function stStealToken()
  {
    return [];
  }

  public function actStealToken()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $ball->setState($player->getTeam());
    Notifications::stealBall($player, $ball);
  }
}
