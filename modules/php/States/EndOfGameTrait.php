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
    $playerWhite = Players::getAll()->filter(fn($player) => $player->getTeam() == WHITE_SIDE)->first();
    $cards = Cards::getInLocation("points-1");
    foreach ($cards as $card) {
      $pointsWhite += $card->getPoints($playerWhite);
      $card->setLocation('revealed-1');
    }
    foreach ($playerWhite->getTeamMembers() as $player) {
      $player->setScore($pointsWhite);
    }
    Notifications::revealPoints($cards, $pointsWhite, WHITE_SIDE);

    // BLACK SCORE
    $pointsBlack = 0;
    $playerBlack = Players::getAll()->filter(fn($player) => $player->getTeam() == BLACK_SIDE)->first();
    $cards = Cards::getInLocation("points-0");
    foreach ($cards as $card) {
      $pointsBlack += $card->getPoints($playerBlack);
      $card->setLocation('revealed-0');
    }
    foreach ($playerBlack->getTeamMembers() as $player) {
      $player->setScore($pointsBlack);
    }
    Notifications::revealPoints($cards, $pointsBlack, BLACK_SIDE);

    $this->gamestate->jumpToState(ST_END_GAME);
  }
}
