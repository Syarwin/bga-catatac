<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Game;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;

class CounterStorage extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_COUNTER_STORAGE;
  }

  public function argsCounterStorage()
  {
    $player = Players::getActive();
    $cards = $player->getHand()->filter(fn($card) => $card->canCounterStorage());

    $helpCards = [];
    foreach ($player->getTeamMembers() as $pId2 => $player2) {
      if ($pId2 == $player->getId()) continue;

      $counterCards = $player2->getHand()->filter(fn($card) => $card->canCounterStorage() && $card->canBePlayedCoop());
      foreach ($counterCards as $cId => $card) {
        $helpCards[$pId2][$cId] = $card->getName();
      }
    }


    return [
      '_private' => [
        'active' => [
          'cardIds' => $cards->getIds(),
          'helpCards' => $helpCards,
        ]
      ]
    ];
  }

  public function actNoCounter()
  {
    $player = Players::getActive();
    $ball = Meeples::getBall();
    $winnerTeam = $ball->getLocation() == WHITE_HIDEOUT ? WHITE_SIDE : BLACK_SIDE;

    // Draw one point cards
    Cards::draw(1, 'deck-points', "points-$winnerTeam");
    Notifications::storage($winnerTeam);

    if (Cards::countInLocation('deck-points') == 0) {
      Notifications::message(clienttranslate("No more point card left in the deck: end of game is triggered!"));
    } else {
      // Flip the ball and place it in the center
      $ball->setState(bga_rand(0, 1));
      $ball->setLocation(NEUTRAL_STREET);
      Notifications::postStorageFlip($ball);

      $this->insertAsChild([
        'pId' => $player->getId(),
        'childs' => [
          [
            'action' => CHOOSE_CARD,
            'pId' => $player->getId(),
          ],
        ],
      ]);
    }

    return true;
  }


  public function actCounterStorage(int $cardId, ?int $pId)
  {
    $args = $this->getArgs();
    $player = Players::getActive();
    $helper = null;

    if (is_null($pId)) {
      if (!in_array($cardId, $args['_private']['active']['cardIds'])) {
        throw new \BgaVisibleSystemException('Invalid card to play. Should not happen');
      }
    } else {
      if (!array_key_exists($cardId, $args['_private']['active']['helpCards'][$pId])) {
        throw new \BgaVisibleSystemException('Invalid card to play from other team members. Should not happen');
      }
      $helper = Players::get($pId);
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

    if (is_null($helper)) {
      Notifications::playCard($player, $card, $n, $isPair);
    } else {
      Notifications::playCardSave($player, $helper, $card, $n, $isPair);
    }
  }
}


    // // Do we have any playable counter card?
    // $counterCards = $player->getHand()->filter(fn($card) => $card->canCounterStorage());
    // if ($counterCards->count() > 0) {
    //   $this->gamestate->jumpToState(ST_START_TURN);
    //   return;
    // }

    // // Does any other team member has a counter coop card?
    // $prevented = false;
    // $helper = null;
    // foreach ($player->getTeamMembers() as $player2) {
    //   $counterCards = $player2->getHand()->filter(fn($card) => $card->canCounterStorage() && $card->canBePlayedCoop());
    //   if (!$counterCards->empty()) {
    //     $helper = $player2;
    //     $card = $counterCards->first();
    //     $prevented = true;
    //     break;
    //   }
    // }

    // // Prevented by another player? Move to next player.
    // if ($prevented) {
    //   $flow = [
    //     'type' => NODE_PARALLEL,
    //     'childs' => [
    //       [
    //         'action' => ACTIVATE_CARD,
    //         'optional' => false,
    //         'args' => ['cardId' => $card->getId()]
    //       ]
    //     ]
    //   ];

    //   // Are we doing a pair?
    //   $n = $card->getNumber();
    //   $previousCard = Cards::getTopDiscardCard();
    //   $oldN = $previousCard->getNumber();
    //   $isPair = $n == 0 || ($n == $oldN);
    //   if ($isPair) {
    //     $flow['childs'][] = ['action' => PAIR_BONUS];
    //   }

    //   // Move card
    //   Cards::insertOnTop($card->getId(), 'discard');
    //   Notifications::playCardSave($player, $helper, $card, $n, $isPair);

    //   Engine::setup($flow, ['method' => 'stEndOfTurn']);
    //   Engine::proceed();
    //   return;
    // }
