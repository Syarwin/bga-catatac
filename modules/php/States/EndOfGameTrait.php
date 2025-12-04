<?php

namespace Bga\Games\Catatac\States;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Players;

trait EndOfGameTrait
{
  public function stPreEndOfGame()
  {
    // WHITE SCORE
    $pointsWhite = 0;
    $auxScore = 0;
    $playerWhite = Players::getAll()->filter(fn($player) => $player->getTeam() == WHITE_SIDE)->first();
    $cards = Cards::getInLocation("points-1");
    foreach ($cards as $card) {
      $pointsWhite += $card->getPoints($playerWhite);
      $auxScore += 100 * $card->getSardines();
      $card->setLocation('revealed-1');
    }
    foreach ($playerWhite->getTeamMembers() as $player) {
      $alpha = $player->getAlpha();
      if (!is_null($alpha)) {
        $auxScore += 100 * $alpha->getSardines();
      }
      $auxScore += $player->getHand()->count();
    }
    foreach ($playerWhite->getTeamMembers() as $player) {
      $player->setScore($pointsWhite);
      $player->setScoreAux($auxScore);
    }
    Notifications::revealPoints($cards, $pointsWhite, WHITE_SIDE, $auxScore);

    // BLACK SCORE
    $pointsBlack = 0;
    $auxScore = 0;
    $playerBlack = Players::getAll()->filter(fn($player) => $player->getTeam() == BLACK_SIDE)->first();
    $cards = Cards::getInLocation("points-0");
    foreach ($cards as $card) {
      $pointsBlack += $card->getPoints($playerBlack);
      $auxScore += 100 * $card->getSardines();
      $card->setLocation('revealed-0');
    }
    foreach ($playerWhite->getTeamMembers() as $player) {
      $alpha = $player->getAlpha();
      if (!is_null($alpha)) {
        $auxScore += 100 * $alpha->getSardines();
      }
      $auxScore += $player->getHand()->count();
    }
    foreach ($playerBlack->getTeamMembers() as $player) {
      $player->setScore($pointsBlack);
      $player->setScoreAux($auxScore);
    }
    Notifications::revealPoints($cards, $pointsBlack, BLACK_SIDE, $auxScore);

    $this->gamestate->jumpToState(ST_END_GAME);
  }
}
