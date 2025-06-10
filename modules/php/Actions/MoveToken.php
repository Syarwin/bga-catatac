<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class MoveToken extends \Bga\Games\Catatac\Models\Action
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
    return [
      'log' => clienttranslate('Move x${n}'),
      'args' => [
        'n' => $this->getN()
      ]
    ];
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

  public function isDoable(Player $player): bool
  {
    $mustOwnTheBall = $this->getCtxArg('mustOwn') ?? false;
    if ($mustOwnTheBall && !$player->isOwningTheBall()) return false;

    $ball = Meeples::getBall();
    $newLocation = $this->getNewLocation($player);
    return in_array($ball->getLocation(), STREETS) && in_array($newLocation, STREETS);
  }

  public function stMoveToken()
  {
    return [];
  }

  public function actMoveToken()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $n = $this->getN();
    $ball->setLocation($this->getNewLocation($player));
    Notifications::moveBall($player, $n, $ball);
  }
}
