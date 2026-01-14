<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Player;

class CounterStorageAction extends \Bga\Games\Catatac\Models\Action
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
    return clienttranslate("Counter hoarding attempt");
  }

  public function isDoable(Player $player): bool
  {
    $ball = Meeples::getBall();
    return  in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]) && $ball->getLocation() != $player->getHideoutLocation();
  }

  public function stCounterStorageAction()
  {
    return [];
  }

  public function getNewLocation(Player $player)
  {
    $ball = Meeples::getBall();
    return $ball->getLocation() + ($player->getTeam() == WHITE_SIDE ? -1 : 1);
  }

  public function actCounterStorageAction()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $ball->setLocation($this->getNewLocation($player));
    Notifications::counterStorage($player, $ball);
  }
}
