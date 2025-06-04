<?php

namespace Bga\Games\Catatac\Helpers;

use Bga\Games\Catatac\Core\Game;
use APP_Object;

class DB_Manager
{
  protected static string $table = "";
  protected static string $primary = "";
  protected static bool $log = true;
  protected static function cast(array $row): mixed
  {
    return $row;
  }

  public static function DB($table = null)
  {
    if (is_null($table)) {
      if (is_null(static::$table)) {
        throw new \feException('You must specify the table you want to do the query on');
      }
      $table = static::$table;
    }

    $log = null;
    if (static::$log ?? true) {
      $log = new Log(static::$table, static::$primary);
    }
    return new QueryBuilder(
      $table,
      function ($row) {
        return static::cast($row);
      },
      static::$primary,
      $log
    );
  }
}
