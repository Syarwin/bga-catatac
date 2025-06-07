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
    $ball = Meeples::getBall();
    if (!in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT])) {
      $this->gamestate->jumpToState(ST_START_TURN);
      return;
    }

    // Do we have any playable counter card?
    $counterCards = $player->getHand()->filter(fn($card) => $card->canCounterStorage());
    if ($counterCards->count() > 0) {
      $this->gamestate->jumpToState(ST_START_TURN);
      return;
    }

    // Does any other team member has a counter coop card?
    $prevented = false;
    foreach ($player->getTeamMembers() as $player2) {
      $counterCards = $player2->getHand()->filter(fn($card) => $card->canCounterStorage() && $card->canBePlayedCoop());
      if (!$counterCards->empty()) {
        $card = $counterCards->first();
        $prevented = true;
        break;
      }
    }

    // Prevented by another player? Move to next player.
    if ($prevented) {
      die("test");
      return;
    }

    $this->stStorageAttemptSuccess();
  }

  function stStartTurn()
  {
    $player = Players::getActive();
    $skipped = Globals::getSkippedPlayers();
    if (in_array($player->getId(), $skipped)) {
      // Any other player left ??
      if (count($skipped) >= Players::count()) {
        Notifications::message(clienttranslate("No more card in hand: end of game is triggered!"));
        $this->gamestate->jumpToState(ST_PRE_END_OF_GAME);
        return;
      }

      $this->nextPlayerCustomOrder('turn');
      return;
    }

    // No cards in hand? => skip the player
    if ($player->getHand()->empty()) {
      $skipped[] = $player->getId();
      Globals::setSkipped($skipped);
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


  public function stStorageAttemptSuccess()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $winnerTeam = $ball->getLocation() == WHITE_HIDEOUT ? WHITE_SIDE : BLACK_SIDE;

    // Draw one point cards
    Cards::draw(1, 'deck-points', "points-$winnerTeam");
    Notifications::storage($winnerTeam);

    if (Cards::countInLocation('deck-points') == 0) {
      Notifications::message(clienttranslate("No more point card left in the deck: end of game is triggered!"));
      $this->gamestate->jumpToState(ST_PRE_END_OF_GAME);
      return;
    }

    // Flip the ball and place it in the center
    $ball->setState(bga_rand(0, 1));
    $ball->setLocation(NEUTRAL_STREET);
    Notifications::postStorageFlip($ball);

    $this->gamestate->jumpToState(ST_START_TURN);
  }
}
