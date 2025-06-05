<?php

namespace Bga\Games\Catatac\Models;

class Card extends \Bga\Games\Catatac\Helpers\DB_Model
{
  protected string $table = 'cards';
  protected string $primary = 'card_id';
  protected array $attributes = [
    'id' => ['card_id', 'int'],
    'location' => 'card_location',
    'state' => ['card_state', 'int'],
    'type' => 'type',
  ];
  protected int $id;
  protected string $location;
  protected int $state;
  protected string $type;

  public function getNumber(): int
  {
    return -1;
  }

  public function getName(): string|array
  {
    return $this->type; // TODO
  }
}
