<?php

namespace Bga\Games\Catatac\Helpers;

use Bga\Games\Catatac\Core\Game;
use Bga\Games\Catatac\Helpers\Collection;

class CachedDB_Manager extends DB_Manager
{
  protected static string $table = "";
  protected static string $primary = "";
  protected static bool $log = true;
  protected static ?Collection $datas = null;
  protected static function cast(array $row): mixed
  {
    return $row;
  }

  public static function fetchIfNeeded()
  {
    if (is_null(static::$datas)) {
      static::$datas = static::DB()->get();
    }
  }

  public static function invalidate()
  {
    static::$datas = null;
  }

  public static function getAll(): ?Collection
  {
    self::fetchIfNeeded();
    return static::$datas;
  }

  public static function get($id)
  {
    return self::getAll()
      ->filter(function ($obj) use ($id) {
        return $obj->getId() == $id;
      })
      ->first();
  }
}
