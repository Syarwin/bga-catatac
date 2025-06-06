<?php

namespace Bga\Games\Catatac\Managers;

use Bga\Games\Catatac\Helpers\CachedPieces;
use Bga\Games\Catatac\Models\Card;
use Bga\Games\Catatac\Helpers\Collection;
use Bga\Games\Catatac\Models\PawnCard;

class Cards extends CachedPieces
{
  protected static string $table = 'cards';
  protected static string $prefix = 'card_';
  protected static array $customFields = ['type'];
  protected static null|Collection $datas = null;
  protected static bool $autoremovePrefix = false;
  protected static bool $autoIncrement = true;

  protected static function cast($row): Card|PawnCard
  {
    $t = explode("-", $row['type']);
    if (is_numeric($t[0])) {
      if ($t[1] == 'Basic') {
        return new PawnCard($row);
      } else {
        $class = 'Bga\Games\Catatac\Models\Cards\\' . $t[1];
        return new $class($row);
      }
    }

    return new Card($row);
  }

  public static function getUiData(): array
  {
    return self::getInLocation('discard')->orderBy('state')->toArray();
  }

  public static function getTopDiscardCard(): Card
  {
    return self::getTopOf('discard', 1)->first();
  }

  public static array $baseGameDeck = [
    [1, CHARGE, 2],
    [1, BASIC, 4],
    [1, SPRINT, 2],
    [1, STOP, 2],

    [2, CAPTURE, 2],
    [2, CHARGE, 2],
    [2, BASIC, 4],
    [2, SPRINT, 2],

    [3, CAPTURE, 2],
    [3, CHARGE, 2],
    [3, BASIC, 4],
    [3, SMASH, 2],

    [4, CAPTURE, 2],
    [4, CHARGE, 2],
    [4, BASIC, 4],
    [4, SMASH, 2],

    [5, CAPTURE, 2],
    [5, BASIC, 4],
    [5, SMASH, 2],
    [5, STOP, 2],

    [6, COURAGE, 2],
    [6, IMPACT, 2],
    [6, MIRACLE, 2],
    [6, BASIC, 4],

    [7, COURAGE, 2],
    [7, IMPACT, 2],
    [7, MIRACLE, 2],
    [7, BASIC, 4],

    [8, BINGO, 2],
    [8, COURAGE, 2],
    [8, FIDO, 2],
    [8, BASIC, 4],

    [0, JOKER, 1],
  ];

  public static $baseGamePointsDeck = [
    "Points-0-Green",
    "Points-1-Blue",
    "Points-1-Grey",
    "Points-1-Yellow",
    "Points-1-Red",
    "Points-Black",
    "Points-2-Multi",
    "Points-White"
  ];

  public static function getCardsLeft(): int
  {
    return self::countInLocation("deck");
  }

  public static function getPointsCardsLeft(): int
  {
    return self::countInLocation("deck-points");
  }

  ////////////////////////////////////
  //  ____       _
  // / ___|  ___| |_ _   _ _ __
  // \___ \ / _ \ __| | | | '_ \
  //  ___) |  __/ |_| |_| | |_) |
  // |____/ \___|\__|\__,_| .__/
  //                      |_|
  ////////////////////////////////////

  /* Creation of all cards */
  public static function setupNewGame(array $players, array $options): void
  {
    // Create and shuffle the deck
    $cards = [];
    $availableCards = static::$baseGameDeck;
    // TODO : handle options here

    foreach ($availableCards as $cardInfo) {
      [$number, $power, $nbr] = $cardInfo;
      $type = $number . "-" . $power;

      $cards[] = [
        'type' => $type,
        'nbr' => $nbr,
      ];
    }

    self::create($cards, 'deck');
    self::shuffle('deck');

    // Draw 6 cards per players
    foreach ($players as $pId => $player) {
      self::draw(6, "deck", "hand-$pId");
    }
    // Draw 1 card into discard
    self::draw(1, "deck", "discard");

    // Create and shuffle the points deck
    $cards = [];
    $availableCards = static::$baseGamePointsDeck;
    // TODO : handle options here

    foreach ($availableCards as $type) {
      $cards[] = [
        'type' => $type,
        'nbr' => 1,
      ];
    }

    self::create($cards, 'deck-points');
    self::shuffle('deck-points');
  }

  /**
   * Draw cards from the deck => ensure state is set
   */
  public static function draw($n = 1, $fromLocation = 'deck', $toLocation = 'hand'): Collection
  {
    $cards = self::pickForLocation($n, $fromLocation, $toLocation);
    foreach ($cards as $cId => &$c) {
      self::insertOnTop($cId, $toLocation);
    }
    return $cards;
  }
}
