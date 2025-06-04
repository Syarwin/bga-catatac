<?php

namespace Bga\Games\Catatac\Managers;

use Bga\Games\Catatac\Helpers\CachedPieces;
use Bga\Games\Catatac\Helpers\Collection;
use Bga\Games\Catatac\Models\Meeple;

/* Class to manage all the meeples for catatac */

class Meeples extends CachedPieces
{
  protected static string $table = 'meeples';
  protected static string $prefix = 'meeple_';
  protected static bool $autoremovePrefix = false;
  protected static array $customFields = ['type', 'player_id'];
  protected static null|Collection $datas = null;

  protected static function cast($row): Meeple
  {
    return new Meeple($row);
  }
  public static function getUiData(): array
  {
    return self::getAll()->toArray();
  }

  ////////////////////////////////////
  //  ____       _
  // / ___|  ___| |_ _   _ _ __
  // \___ \ / _ \ __| | | | '_ \
  //  ___) |  __/ |_| |_| | |_) |
  // |____/ \___|\__|\__,_| .__/
  //                      |_|
  ////////////////////////////////////

  /* Creation of various meeples */
  public static function setupNewGame(array $players, array $options)
  {
    // $meeples = [];
    // for ($i = 0; $i < 8; $i++) {
    //   $meeples[] = ['type' => COAL, 'location' => "market-coal-$i"];
    //   $meeples[] = ['type' => IRON, 'location' => "market-iron-$i"];
    // }

    // return self::create($meeples);
  }
}
