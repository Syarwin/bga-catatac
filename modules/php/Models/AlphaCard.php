<?php

namespace Bga\Games\Catatac\Models;

use Bga\Games\Catatac\Managers\Cards;

class AlphaCard extends PawnCard
{
  public function getNumber(): int
  {
    return ALPHA_NUMBER;
  }

  protected string $name = "";
  public function getName(): string
  {
    return $this->name;
  }

  public function canBePlayed(Player $player): bool
  {
    return Cards::countInLocation("deck") > 0;
  }

  public function isAlpha(): bool
  {
    return true;
  }
}
