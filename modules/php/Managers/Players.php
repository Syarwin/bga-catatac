<?php

namespace Bga\Games\Catatac\Managers;

use Bga\Games\Catatac\Game;
use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Helpers\Collection;
use Bga\Games\Catatac\Models\Player;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \Bga\Games\Catatac\Helpers\CachedDB_Manager
{
  protected static string $table = 'player';
  protected static string $primary = 'player_id';
  protected static ?Collection $datas = null;

  protected static function cast(array $row): Player
  {
    return new \Bga\Games\Catatac\Models\Player($row);
  }

  public static function setupNewGame($players, $options)
  {
    // Create players
    // $gameInfos = Game::get()->getGameinfos();
    // $colors = $gameInfos['player_colors'];
    $colors = ["d1ac51", "95a0b2", "8d4129", "9374ad"];
    $query = self::DB()->multipleInsert([
      'player_id',
      'player_color',
      'player_canal',
      'player_name',
      'player_avatar',
      'team',
    ]);
    $playerIndex = 0;
    $values = [];
    foreach ($players as $pId => $player) {
      $color = $colors[$playerIndex];
      $values[] = [
        $pId,
        $color,
        $player['player_canal'],
        $player['player_name'],
        $player['player_avatar'],
        $playerIndex % 2,
      ];
      $playerIndex++;
    }
    $query->values($values);
    self::invalidate();
    Game::get()->reattributeColorsBasedOnPreferences($players, $colors);
    Game::get()->reloadPlayersBasicInfos();
  }

  public static function getActiveId()
  {
    return (int) Game::get()->getActivePlayerId();
  }

  public static function getCurrentId($bReturnNullIfNotLogged = false)
  {
    return (int) Game::get()->getCurrentPId($bReturnNullIfNotLogged);
  }

  public static function getActive(): Player
  {
    return self::get(self::getActiveId());
  }

  public static function getCurrent(): Player
  {
    return self::get(self::getCurrentId());
  }

  public static function get($id = null)
  {
    return parent::get($id ?? self::getActiveId());
  }

  public static function getNextId($player)
  {
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }

  public static function getNext($player)
  {
    return self::get(self::getNextId($player));
  }

  /*
   * Return the number of players
   */
  public static function count()
  {
    return self::getAll()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId = null)
  {
    return self::getAll()
      ->map(fn($player) => $player->getUiData($pId))
      ->toAssoc();
  }

  /*
   * Get current turn order according to first player variable
   */
  public static function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }
}
