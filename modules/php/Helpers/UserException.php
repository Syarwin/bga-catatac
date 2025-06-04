<?php

namespace Bga\Games\Catatac\Helpers;

use Bga\Games\Catatac\Game;

class UserException extends \BgaUserException
{
  public function __construct($str)
  {
    parent::__construct(Game::get()::translate($str));
  }
}
