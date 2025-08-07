<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;

class ChooseCard extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_CHOOSE_CARD;
  }

  public function argsChooseCard()
  {
    $player = Players::getActive();
    $cards = $player->getHand();

    $ball = Meeples::getBall();
    $counterStorage = false;
    if (in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT])) {
      $counterStorage = true;
      $cards = $cards->filter(fn($card) => $card->canCounterStorage());
    }

    // Highlight useful cards
    $previousCard = Cards::getTopDiscardCard();
    $oldN = $previousCard->getNumber();
    $usefulCards = $cards->filter(fn($card) => $card->canTakeActionBloc($player) || $card->getNumber() == $oldN || $card->getNumber() == 0);

    return [
      'counterStorage' => $counterStorage,
      '_private' => [
        'active' => [
          'cardIds' => $cards->getIds(),
          'usefulCardIds' => $usefulCards->getIds(),
        ]
      ]
    ];
  }

  public function actChooseCard(int $cardId)
  {
    $args = $this->getArgs();
    $player = Players::getActive();

    if (!in_array($cardId, $args['_private']['active']['cardIds'])) {
      throw new \BgaVisibleSystemException('Invalid card to play. Should not happen');
    }

    // Are we doing a pair?
    $card = Cards::getSingle($cardId);
    $n = $card->getNumber();
    $previousCard = Cards::getTopDiscardCard();
    $oldN = $previousCard->getNumber();
    $isPair = $n == 0 || ($n == $oldN);

    // Move card
    Cards::insertOnTop($cardId, 'discard');

    if ($isPair) {
      $this->pushParallelChild(['action' => PAIR_BONUS]);
    }

    // Any action bloc?
    if (!empty($card->getActionBloc())) {
      $ball = Meeples::getBall();
      $optional = !in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);

      $this->pushParallelChild([
        'action' => ACTIVATE_CARD,
        'optional' => $optional,
        'args' => ['cardId' => $cardId]
      ]);
    }

    $changeSide = false;
    if ($card->getType() == "0-JokerExclusive") {
      $flipped = Globals::getFlippedBoard();
      Globals::setFlippedBoard(!$flipped);
      $changeSide = true;
    }
    Notifications::playCard($player, $card, $n, $isPair, $changeSide);
  }
}
