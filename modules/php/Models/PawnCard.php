<?php

namespace Bga\Games\Catatac\Models;

class PawnCard extends Card
{
  public function getNumber(): int
  {
    return (int) explode("-", $this->type)[0];
  }

  public function getName(): string|array
  {
    $action = explode("-", $this->type)[1];
    $number = $this->getNumber();

    if ($number == 0) {
      return clienttranslate("Joker");
    }
    if ($action == "Basic") {
      return "<$number-black>";
    }

    return $this->type; // TODO
  }
}
