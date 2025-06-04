<?php

namespace Bga\Games\Catatac\Managers;

use Bga\Games\Catatac\Helpers\CachedPieces;
use Bga\Games\Catatac\Models\Card;
use Bga\Games\Catatac\Helpers\Collection;

class Cards extends CachedPieces
{
  protected static string $table = 'cards';
  protected static string $prefix = 'card_';
  protected static array $customFields = ['type'];
  protected static null|Collection $datas = null;
  protected static bool $autoremovePrefix = false;
  protected static bool $autoIncrement = true;

  protected static function cast($row): Card
  {
    return new Card($row);
  }

  public static function getUiData()
  {
    return [];
  }

  public static function getCardsLeft()
  {
    return self::countInLocation("deck");
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
    // // Create and shuffle the deck
    // $cards = [];
    // $nPlayers = count($players);
    // foreach (self::$deck as $type => $numbers) {
    //   $cards[] = [
    //     'type' => $type,
    //     'nbr' => $numbers[$nPlayers - 2],
    //   ];
    // }

    // self::create($cards, 'deck');
    // self::shuffle('deck');

    // // Draw 8 cards per players
    // foreach ($players as $pId => $player) {
    //   self::draw($player, 8, "deck", "hand-$pId");
    // }
  }

  /**
   * Draw cards from the deck => ensure state is set
   */
  public static function draw($player, $n = 1, $fromLocation = 'deck', $toLocation = 'hand'): Collection
  {
    $cards = self::pickForLocation($n, $fromLocation, $toLocation);
    foreach ($cards as $cId => &$c) {
      self::insertOnTop($cId, $toLocation);
    }
    return $cards;
  }
}
