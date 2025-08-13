<?php

namespace Bga\Games\Catatac\Core;

use Bga\Games\Catatac\Game;
use Bga\Games\Catatac\Helpers\Collection;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Models\Card;
use Bga\Games\Catatac\Models\Meeple;
use Bga\Games\Catatac\Models\Player;
use Bga\Games\Catatac\Models\Tile;


class Notifications
{
  public static function playCard(Player $player, Card $card, int $n, bool $isPair, bool $changeSide)
  {
    $msg = $isPair ?
      clienttranslate('${teamIcon}${player_name} plays a ${card_name}, making a pair!') :
      clienttranslate('${teamIcon}${player_name} plays a ${card_name}');

    $data = [
      'player' => $player,
      'card' => $card,
    ];
    if ($changeSide) {
      $data['flippedBoard'] = Globals::getFlippedBoard();
    }

    if ($card->isAlpha()) {
      $msg = clienttranslate('${teamIcon}${player_name} plays their Alpha: ${card_name}');
      $data['alpha'] = true;
    }

    self::notifyAll('discardCard', $msg, $data);
  }

  public static function playCardSave(Player $player, Player $player2, Card $card, int $n, bool $isPair)
  {
    $msg = $isPair ?
      clienttranslate('${teamIcon}${player_name} plays a ${card_name} from ${player_name2}\'s hand, making a pair!') :
      clienttranslate('${teamIcon}${player_name} plays a ${card_name} from ${player_name2}\'s hand');

    self::notifyAll('discardCardTeammate', $msg, [
      'player' => $player,
      'player2' => $player2,
      'card' => $card,
    ]);
  }

  public static function replenishCards(Player $player, Collection $cards)
  {
    self::drawCards($player, $cards);
  }

  public static function drawCards(Player $player, Collection $cards, ?string $privateMsg = null, ?string $publicMsg = null, array $args = [])
  {
    self::notify(
      $player,
      'pDrawCards',
      $privateMsg ?? clienttranslate('${player_name}: you draw ${card_names} from the deck'),
      $args + [
        'player' => $player,
        'cards' => is_array($cards) ? $cards : $cards->toArray(),
      ]
    );
    self::notifyAll(
      'drawCards',
      $publicMsg ?? clienttranslate('${player_name} draws ${n} card(s) from the deck'),
      $args + [
        'player' => $player,
        'n' => count($cards),
        'ignore' => $player->getId(),
      ]
    );
  }

  public static function moveBall(Player $player, int $n, Meeple $ball)
  {
    $msgs = [
      WHITE_STREET => clienttranslate('${teamIcon}${player_name} moves the ball ${n} step(s) to the white street'),
      NEUTRAL_STREET => clienttranslate('${teamIcon}${player_name} moves the ball ${n} step(s) to the neutral street'),
      BLACK_STREET => clienttranslate('${teamIcon}${player_name} moves the ball ${n} step(s) to the black street'),
    ];

    $newLocation = $ball->getLocation();
    self::notifyAll('moveBall', $msgs[$newLocation], [
      'player' => $player,
      'n' => $n,
      'location' => $newLocation,
    ]);
  }

  public static function storageAttempt($player, $ball)
  {
    $newLocation = $ball->getLocation();
    self::notifyAll('moveBall', clienttranslate('${teamIcon}${player_name} makes a storage attempt!'), [
      'player' => $player,
      'location' => $newLocation,
    ]);
  }

  public static function counterStorage($player, $ball)
  {
    $newLocation = $ball->getLocation();
    self::notifyAll('moveBall', clienttranslate('${teamIcon}${player_name} counters the storage attempt!'), [
      'player' => $player,
      'location' => $newLocation,
    ]);
  }

  public static function stealBall(Player $player, Meeple $ball)
  {
    self::notifyAll('stealBall', clienttranslate('${teamIcon}${player_name} steals the ball'), [
      'player' => $player,
      'side' => $ball->getState(),
    ]);
  }

  public static function tossBall(Player $player, Meeple $ball)
  {
    self::notifyAll('tossBall', clienttranslate('${teamIcon}${player_name} tosses the ball'), [
      'player' => $player,
      'ball' => $ball,
    ]);
  }

  public static function storage(int $winnerTeam)
  {
    $msg = $winnerTeam == WHITE_SIDE ? clienttranslate('Black team does not counter the storage, white team <cat-white> gains a points card!') :
      clienttranslate('White team does not counter the storage, black team <cat-black> gains a points card!');

    self::notifyAll('storage', $msg, [
      'team' => $winnerTeam
    ]);
  }

  public static function postStorageFlip(Meeple $ball)
  {
    self::notifyAll('postStorageFlip', clienttranslate('Flipping and returning the ball to the center'), [
      'ball' => $ball,
    ]);
  }

  public static function revealPoints(Collection $cards, int $score, int $team)
  {
    $msg = $team == WHITE_SIDE ? clienttranslate('<cat-white> White team has ${n} point card(s), scoring a total of ${score} points! <cat-white>') :
      clienttranslate('<cat-black> White team has ${n} point card(s), scoring a total of ${score} points! <cat-black>');

    self::notifyAll('revealPoints', $msg, [
      'n' => $cards->count(),
      'cards' => $cards->toArray(),
      'score' => $score,
      'team' => $team,
    ]);
  }


  ////////////////////////////////////////////
  //   ____                      _      
  //  / ___| ___ _ __   ___ _ __(_) ___ 
  // | |  _ / _ \ '_ \ / _ \ '__| |/ __|
  // | |_| |  __/ | | |  __/ |  | | (__ 
  //  \____|\___|_| |_|\___|_|  |_|\___|
  ////////////////////////////////////////////

  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    self::updateIfNeeded($data, $name, 'public');
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data, $name, 'private');
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('midmessage', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  public static function newUndoableStep($player, $stepId)
  {
    self::notify($player, 'newUndoableStep', clienttranslate('Undo here'), [
      'stepId' => $stepId,
      'preserve' => ['stepId'],
    ]);
  }

  public static function clearTurn($player, $notifIds)
  {
    self::notifyAll('clearTurn', clienttranslate('${player_name} restarts their turn'), [
      'player' => $player,
      'notifIds' => $notifIds,
    ]);
  }

  public static function refreshUI(array $datas)
  {
    // // Keep only the thing that matters
    $fDatas = [
      'players' => $datas['players'],
      'cards' => $datas['cards'],
      'meeples' => $datas['meeples'],
      'flippedBoard' => $datas['flippedBoard'],
    ];

    foreach ($fDatas['players'] as &$player) {
      $player['hand'] = []; // Hide hand !
    }

    self::notifyAll('refreshUI', '', [
      'datas' => $fDatas,
    ]);
  }

  public static function refreshHand(Player $player, Collection $hand)
  {
    self::notify($player, 'refreshHand', '', [
      'player' => $player,
      'hand' => $hand->toArray(),
      'alpha' => $player->getAlpha(),
    ]);
  }

  /////////////////////////////////////
  //    ____           _          
  //   / ___|__ _  ___| |__   ___ 
  //  | |   / _` |/ __| '_ \ / _ \
  //  | |__| (_| | (__| | | |  __/
  //   \____\__,_|\___|_| |_|\___|
  /////////////////////////////////////

  protected static $listeners = [
    // [
    //   'name' => 'income',
    //   'player' => true,
    //   'method' => 'getMoneyIncome',
    // ],
    // [
    //   'name' => 'score',
    //   'player' => true,
    //   'method' => 'updateScore',
    // ],
  ];
  protected static $ignoredNotifs = [];

  protected static $cachedValues = [];
  public static function resetCache()
  {
    foreach (self::$listeners as $listener) {
      $method = $listener['method'];
      if ($listener['player'] ?? false) {
        foreach (Players::getAll() as $pId => $player) {
          self::$cachedValues[$listener['name']][$pId] = $player->$method();
        }
      } else {
        self::$cachedValues[$listener['name']] = call_user_func($method);
      }
    }
  }

  public static function updateIfNeeded(&$args, $notifName, $notifType)
  {
    foreach (self::$listeners as $listener) {
      $name = $listener['name'];
      $method = $listener['method'];

      if ($listener['player'] ?? false) {
        foreach (Players::getAll() as $pId => $player) {
          $val = $player->$method();
          if ($val !== (self::$cachedValues[$name][$pId] ?? null)) {
            $args['infos'][$name][$pId] = $val;
            // Only bust cache when a public non-ignored notif is sent to make sure everyone gets the info
            if ($notifType == 'public' && !in_array($notifName, self::$ignoredNotifs)) {
              self::$cachedValues[$name][$pId] = $val;
            }
          }
        }
      } else {
        $val = call_user_func($method);
        if ($val !== (self::$cachedValues[$name] ?? null)) {
          $args['infos'][$name] = $val;
          // Only bust cache when a public non-ignored notif is sent to make sure everyone gets the info
          if ($notifType == 'public' && !in_array($notifName, self::$ignoredNotifs)) {
            self::$cachedValues[$name] = $val;
          }
        }
      }
    }
  }




  ///////////////////////////////////////////////////////////////
  //  _   _           _       _            _
  // | | | |_ __   __| | __ _| |_ ___     / \   _ __ __ _ ___
  // | | | | '_ \ / _` |/ _` | __/ _ \   / _ \ | '__/ _` / __|
  // | |_| | |_) | (_| | (_| | ||  __/  / ___ \| | | (_| \__ \
  //  \___/| .__/ \__,_|\__,_|\__\___| /_/   \_\_|  \__, |___/
  //       |_|                                      |___/
  ///////////////////////////////////////////////////////////////

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      $data['teamIcon'] = $data['player']->getTeam() == WHITE_SIDE ? '<cat-white>' : '<cat-black>';
      $data['i18n'][] = 'teamIcon';
      unset($data['player']);
    }
    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
    if (isset($data['player3'])) {
      $data['player_name3'] = $data['player3']->getName();
      $data['player_id3'] = $data['player3']->getId();
      unset($data['player3']);
    }
    if (isset($data['players'])) {
      $args = [];
      $logs = [];
      foreach ($data['players'] as $i => $player) {
        $logs[] = '${player_name' . $i . '}';
        $args['player_name' . $i] = $player->getName();
      }
      $data['players_names'] = [
        'log' => join(', ', $logs),
        'args' => $args,
      ];
      $data['i18n'][] = 'players_names';
      unset($data['players']);
    }

    if (isset($data['card'])) {
      $data['card_id'] = $data['card']->getId();
      $data['card_name'] = $data['card']->getName();
      $data['i18n'][] = 'card_name';
      $data['preserve'][] = 'card_id';
    }

    if (isset($data['cards'])) {
      $args = [];
      $logs = [];
      foreach ($data['cards'] as $i => $card) {
        $logs[] = '${card_name_' . $i . '}';
        $args['i18n'][] = 'card_name_' . $i;
        $args['card_name_' . $i] = [
          'log' => '${card_name}',
          'args' => [
            'i18n' => ['card_name'],
            'card_name' => is_array($card) ? $card['name'] : $card->getName(),
          ],
        ];
      }
      $data['card_names'] = [
        'log' => join(', ', $logs),
        'args' => $args,
      ];
      $data['i18n'][] = 'card_names';
    }
  }
}
