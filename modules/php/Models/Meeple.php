<?php

namespace Bga\Games\Catatac\Models;

class Meeple extends \Bga\Games\Catatac\Helpers\DB_Model
{
  protected string $table = 'meeples';
  protected string $primary = 'meeple_id';
  protected array $attributes = [
    'id' => ['meeple_id', 'int'],
    'location' => 'meeple_location',
    'state' => ['meeple_state', 'int'],
    'pId' => ['player_id', 'int'],
    'type' => 'type',
  ];
  protected int $id;
  protected string $location;
  protected int $state;
  protected string $type;
  protected ?int $pId;
}
