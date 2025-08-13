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
    return ST_MOVE_TOKEN;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    $player = $player ?? Players::getActive();
    return count($this->getNewLocations($player)) == 1;
  }

  public function getDescription(): string|array
  {
    return [
      'log' => clienttranslate('Move x${n}'),
      'args' => [
        'n' => implode('/', $this->getN())
      ]
    ];
  }

  public function getN(): array
  {
    $m = $this->getCtxArg('n') ?? 1;
    return is_array($m) ? $m : [$m];
  }

  public function getNewLocations(Player $player)
  {
    $ball = Meeples::getBall();
    $currentLocation = $ball->getLocation();

    $forcedDirection = false;
    $dirs = $forcedDirection ? [$player->getTeam() == WHITE_SIDE ? -1 : 1] : [-1, 1];
    $locations = [];
    $n = $this->getN();
    foreach ($dirs as $dir) {
      foreach ($n as $m) {
        $newLocation = $currentLocation + $dir * $m;
        if (in_array($newLocation, STREETS)) {
          $locations[] = $newLocation;
        }
      }
    }

    return $locations;
  }

  public function argsMoveToken()
  {
    $player = Players::getActive();
    return [
      'locations' => $this->getNewLocations($player)
    ];
  }

  public function isDoable(Player $player): bool
  {
    $mustOwnTheBall = $this->getCtxArg('mustOwn') ?? false;
    if ($mustOwnTheBall && !$player->isOwningTheBall()) return false;

    $ball = Meeples::getBall();
    return in_array($ball->getLocation(), STREETS) && !empty($this->getNewLocations($player));
  }

  public function stMoveToken()
  {
    $newLocations = $this->getNewLocations(Players::getActive());
    if (count($newLocations) == 1) {
      return [$newLocations[0]];
    }
  }

  public function actMoveToken($newLocation)
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $n = abs($newLocation - $ball->getLocation());
    $ball->setLocation($newLocation);
    Notifications::moveBall($player, $n, $ball);
  }
}
