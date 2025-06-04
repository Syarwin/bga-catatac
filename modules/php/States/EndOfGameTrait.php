<?php

namespace Bga\Games\Catatac\States;


trait EndOfGameTrait
{
  public function stPreEndOfGame()
  {
    $this->gamestate->jumpToState(ST_END_GAME);
  }
}
