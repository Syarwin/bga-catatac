<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Cards;
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
    return [
      '_private' => [
        'active' => [
          'cardIds' => $player->getHand()->getIds()
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
      $this->pushParallelChild(['action' => PAIR_BONUS, 'optional' => true]);
    }

    // Any action bloc?
    if (!empty($card->getActionBloc())) {
      $this->pushParallelChild(['action' => ACTIVATE_CARD, 'optional' => true, 'args' => ['cardId' => $cardId]]);
    }

    Notifications::playCard($player, $card, $n, $isPair);
  }
}
