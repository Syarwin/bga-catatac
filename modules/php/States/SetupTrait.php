<?php

namespace Bga\Games\Catatac\States;

use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Core\Stats;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Managers\Tiles;

trait SetupTrait
{
  /*
   * setupNewGame:
   */
  protected function setupNewGame($players, $options = [])
  {
    Globals::setupNewGame($players, $options);
    Players::setupNewGame($players, $options);
    Cards::setupNewGame($players, $options);
    Meeples::setupNewGame($players, $options);
    Stats::checkExistence();

    Globals::setFirstPlayer($this->getNextPlayerTable()[0]);

    $firstPlayer = Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = Players::getNextId($p);
    } while ($p != $firstPlayer);
    Globals::setTurnOrder($order);

    $this->activeNextPlayer();
  }


  // SETUP BRANCH : might be useful for later and can be used for debugging launch
  public function stSetupBranch()
  {
    $debug = true;
    if ($debug) {
      $this->gamestate->jumpToState(ST_SETUP_DEBUG);
    } else {
      $this->gamestate->jumpToState(ST_NEW_ROUND);
    }
  }
}
