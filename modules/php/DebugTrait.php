<?php

namespace Bga\Games\Catatac;

use Bga\Games\Catatac\Core\Engine;
use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Helpers\Log;

trait DebugTrait
{
  function undoToStep($stepId)
  {
    Log::undoToStep($stepId);
  }

  function tp() {}


  function resolveDebug()
  {
    Engine::resolveAction([]);
    Engine::proceed();
  }

  function engDisplay()
  {
    var_dump(Globals::getEngine());
  }

  function engProceed()
  {
    Engine::proceed();
  }
}
