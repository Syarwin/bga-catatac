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
use Bga\Games\Catatac\Managers\Meeples;

trait TurnTrait
{
  function stPreStartTurn()
  {
    $player = Players::getActive();
    if (Globals::isDistracted()) {
      Notifications::message(clienttranslate('${player_name} is distracted, their turn is skipped!'), ['player' => $player]);
      Globals::setDistracted(false);
      $this->nextPlayerCustomOrder('turn');
      return;
    }

    $ball = Meeples::getBall();
    if (!in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT])) {
      $this->gamestate->jumpToState(ST_START_TURN);
      return;
    }

    self::giveExtraTime($player->getId());
    Engine::setup([
      'pId' => $player->getId(),
      'childs' => [
        [
          'action' => COUNTER_STORAGE_CHOOSE_CARD,
          'pId' => $player->getId(),
        ],
      ],
    ], ['method' => 'stEndOfTurn']);
    Engine::proceed();
  }

  function stStartTurn()
  {
    $player = Players::getActive();
    $skipped = Globals::getSkippedPlayers();
    if (in_array($player->getId(), $skipped)) {
      // Any other player left ??
      if (count($skipped) >= Players::count()) {
        Notifications::message(clienttranslate("No more cards in hand: end of game is triggered!"));
        $this->gamestate->jumpToState(ST_PRE_END_OF_GAME);
        return;
      }

      $this->nextPlayerCustomOrder('turn');
      return;
    }

    // No cards in hand? => skip the player
    if ($player->getHand()->empty()) {
      $skipped[] = $player->getId();
      Globals::setSkippedPlayers($skipped);
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

    // End of game
    if (Cards::countInLocation('deck-points') == 0) {
      $this->gamestate->jumpToState(ST_PRE_END_OF_GAME);
      return;
    }

    // Refill to 6 cards
    if (Cards::countInLocation('deck') > 0) {
      $pId = $player->getId();
      $nCardsToDraw = 6 - $player->getHand()->count();
      if ($nCardsToDraw > 0) {
        $cards = Cards::draw($nCardsToDraw, "deck", "hand-$pId");
        Notifications::replenishCards($player, $cards);
      }
    }

    $this->nextPlayerCustomOrder('turn');
  }
}
