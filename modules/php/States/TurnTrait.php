<?php

namespace Bga\Games\Catatac\States;

use \Bga\GameFramework\Actions\CheckAction;
use \Bga\GameFramework\Actions\Types\IntArrayParam;

use Bga\Games\Catatac\Board;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Core\Stats;
use Bga\Games\Catatac\Core\Engine;
use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Core\Notifications;

trait TurnTrait
{
  /**
   * Custom turn order for player's individual turns
   */
  function stStartTurn()
  {
    $player = Players::getActive();
    $skipped = Globals::getSkippedPlayers();
    if (in_array($player->getId(), $skipped)) {
      $this->nextPlayerCustomOrder('turn');
      return;
    }

    // Give extra time
    self::giveExtraTime($player->getId());

    // Setup engine
    $node = [
      'pId' => $player->getId(),
      'childs' => [
        [
          'action' => CHOOSE_CARD,
          'pId' => $player->getId(),
        ],
      ],
    ];

    // Inserting leaf Action card
    Engine::setup($node, ['method' => 'stEndOfTurn']);
    Engine::proceed();
  }

  public function stEndOfTurn()
  {
    $player = Players::getActive();

    // Refill to 6 cards
    if (Cards::countInLocation('deck') > 0) {
      $pId = $player->getId();
      $nCardsToDraw = 6 - $player->getHand()->count();
      $cards = Cards::draw($nCardsToDraw, "deck", "hand-$pId");
      Notifications::replenishCards($player, $cards);
    }

    $this->nextPlayerCustomOrder('turn');
  }


  /**
   * End of a round: reorder, income phase and check if end of era is needed or not
   */
  public function stEndOfRound()
  {
    // Reorder based on spend
    $moneySpent = [];
    foreach (Players::getTurnOrder() as $pId) {
      $m = Players::get($pId)->getMoneySpent();
      $moneySpent[$m][] = $pId;
    }
    ksort($moneySpent);
    $newTurnOrder = [];
    foreach ($moneySpent as $m => $pIds) {
      foreach ($pIds as $pId) {
        $newTurnOrder[] = $pId;
      }
    }
    foreach (Players::getAll() as $player) {
      $player->setMoneySpent(0);
    }
    Globals::setTurnOrder($newTurnOrder);
    Notifications::newTurnOrder($newTurnOrder);

    // Income, unless game is over
    if (Globals::isLastRound()) {
      $this->stEndOfEra();
      return;
    }

    $isLastRoundOfEra = Globals::getRound() == Globals::getMaxRounds();
    Notifications::startingIncomePhase();
    $this->initCustomDefaultTurnOrder('income', ST_INCOME_PHASE, $isLastRoundOfEra ? 'stEndOfEra' : 'stNewRound');
  }
}
