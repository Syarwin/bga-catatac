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
    // ALPHA
    if (in_array($t[0], ['AlphaWhite', 'AlphaBlack'])) {
      $class = 'Bga\Games\Catatac\Models\AlphaCards\\' . $t[1];
      return new $class($row);
    }
    // PAWN
    if (is_numeric($t[0])) {
      if ($t[1] == 'Basic') {
        return new PawnCard($row);
      } else {
        $class = 'Bga\Games\Catatac\Models\Cards\\' . $t[1];
        return new $class($row);
      }
    }
    // POINTS
    if ($t[0] == "Points") {
      array_shift($t);
      $class = 'Bga\Games\Catatac\Models\PointCards\\' . join("", $t);
      return new $class($row);
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
    [1, CHARGE, 1],
    [1, BASIC, 4],
    [1, SPRINT, 1],
    [1, STOP, 1],

    [2, CAPTURE, 1],
    [2, CHARGE, 1],
    [2, BASIC, 4],
    [2, SPRINT, 1],

    [3, CAPTURE, 1],
    [3, CHARGE, 1],
    [3, BASIC, 4],
    [3, SMASH, 1],

    [4, CAPTURE, 1],
    [4, CHARGE, 1],
    [4, BASIC, 4],
    [4, SMASH, 1],

    [5, CAPTURE, 1],
    [5, BASIC, 4],
    [5, SMASH, 1],
    [5, STOP, 1],

    [6, COURAGE, 1],
    [6, IMPACT, 1],
    [6, MIRACLE, 1],
    [6, BASIC, 4],

    [7, COURAGE, 1],
    [7, IMPACT, 1],
    [7, MIRACLE, 1],
    [7, BASIC, 4],

    [8, BINGO, 1],
    [8, COURAGE, 1],
    [8, FIDO, 1],
    [8, BASIC, 4],

    [0, JOKER, 2],
    [0, JOKER_EXCLUSIVE, 1],
  ];

  public static $baseGamePointsDeck = [
    "Points-Green-0",
    "Points-Blue-1",
    "Points-Grey-1",
    "Points-Yellow-1",
    "Points-Red-1",
    "Points-Black",
    "Points-Multi-2",
    "Points-White"
  ];

  public static $extraCards = [
    OPTION_DISTRACTION => [
      [1, BRUTE, 1],
      [2, BICYCLE, 1],
      [3, DRIBBLE, 1],
      [4, DRIBBLE, 1],
      [5, RAT, 1],
      [6, ZZZZ, 1],
      [7, BEURGH, 1],
      [8, NINJA, 1],
      [ALPHA_NUMBER, BENGAIL, 1, BLACK_SIDE],
      [ALPHA_NUMBER, BENGAIL, 1, WHITE_SIDE],
      [POINT_NUMBER, "Points-Purple-2", 1],
    ],
    OPTION_MOMENTUM => [
      [1, SYNCHRO, 1],
      [2, DUO, 1],
      [3, DUO, 1],
      [4, ACCIDENT, 1],
      [5, REFLEXES, 1],
      [6, MISSION, 1],
      [7, SCHRODINGER, 1],
      [8, AGILE, 1],
      [ALPHA_NUMBER, RAGDOLL, 1, BLACK_SIDE],
      [ALPHA_NUMBER, RAGDOLL, 1, WHITE_SIDE],
      [POINT_NUMBER, "Points-YellowMomentum-1", 1],
    ],
    OPTION_ALPHA => [
      [ALPHA_NUMBER, ANGORA, 1, WHITE_SIDE],
      [ALPHA_NUMBER, MAINE_COON, 1, WHITE_SIDE],
      [ALPHA_NUMBER, MANX, 1, WHITE_SIDE],
      [ALPHA_NUMBER, ANGORA, 1, BLACK_SIDE],
      [ALPHA_NUMBER, LYKOI, 1, BLACK_SIDE],
      [ALPHA_NUMBER, LAPERM, 1, BLACK_SIDE],
      [POINT_NUMBER, "Points-Pasta-Left", 1],
      [POINT_NUMBER, "Points-Pasta-Right", 1],
    ]
  ];

  public static function getCardsCount(): int
  {
    return self::countInLocation("deck") + self::countInLocation("discard") + self::countInLocation("hand-%");
  }
  public static function getPointsCardsCount(): int
  {
    return self::countInLocation("deck-points") + self::countInLocation("points-0") + self::countInLocation("points-1");
  }

  public static function getCardsLeft(): int
  {
    return self::countInLocation("deck");
  }

  public static function getPointsCardsLeft(): int
  {
    return self::countInLocation("deck-points");
  }

  public static function getOwnPointsCardsCounts(): array
  {
    return [
      self::countInLocation('points-0'),
      self::countInLocation('points-1')
    ];
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
    $cards = [];
    $pointCards = [];
    $alphaCards = [];

    // Which boosters are we using?
    $availableCards = static::$baseGameDeck;
    foreach (self::$extraCards as $optionId => $extraCards) {
      if (($options[$optionId] ?? 0) == 0) {
        continue;
      }

      // Handle alpha in other boosters
      foreach ($extraCards as $cardInfo) {
        if ($cardInfo[0] == ALPHA_NUMBER && ($options[OPTION_ALPHA] ?? 0) == 0) {
          continue;
        }

        $availableCards[] = $cardInfo;
      }
    }

    // Separate cards into pawn/points/alpha
    foreach ($availableCards as $cardInfo) {
      [$number, $power, $nbr] = $cardInfo;
      // Alpha
      if ($number == ALPHA_NUMBER) {
        $teamId = $cardInfo[3];
        $team = $teamId == BLACK_SIDE ? 'AlphaBlack' : 'AlphaWhite';
        $alphaCards[] = [
          'type' => "$team-$power",
          'nbr' => $nbr,
          'location' => "deck-alpha-$teamId",
        ];
      }
      // Points
      else if ($number == POINT_NUMBER) {
        $pointCards[] = [
          'type' => $power,
          'nbr' => $nbr,
        ];
      }
      // Pawn
      else {
        $type = $number . "-" . $power;
        $cards[] = [
          'type' => $type,
          'nbr' => $nbr,
        ];
      }
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
    $availableCards = static::$baseGamePointsDeck;
    foreach ($availableCards as $type) {
      $pointCards[] = [
        'type' => $type,
        'nbr' => 1,
      ];
    }

    // Draw 1 alpha if needed
    if (count($alphaCards) > 0) {
      self::create($alphaCards);
      self::shuffle('deck-alpha-0');
      self::shuffle('deck-alpha-1');
      foreach (Players::getAll() as $pId => $player) {
        $team = $player->getTeam();
        self::draw(1, "deck-alpha-$team", "alpha-$pId");
      }
    }

    self::create($pointCards, 'deck-points');
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
