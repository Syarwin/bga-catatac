<?php

namespace Bga\Games\Catatac\Actions;

class ChooseCard extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_CHOOSE_CARD;
  }
}
