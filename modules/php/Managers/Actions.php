<?php

namespace Bga\Games\Catatac\Managers;

use Bga\Games\Catatac\Game;
use Bga\Games\Catatac\Core\Engine;
use Bga\Games\Catatac\Core\Globals;
use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Helpers\Log;
use Bga\Games\Catatac\Helpers\Utils;

/* Class to manage all the Actions */

class Actions
{
  static $classes = [
    CHOOSE_CARD => 'ChooseCard',
    COUNTER_STORAGE_CHOOSE_CARD => 'CounterStorage',
    PAIR_BONUS => 'PairBonus',
    STEAL_TOKEN => 'StealToken',
    MOVE_TOKEN => 'MoveToken',
    STORAGE_ATTEMPT => 'StorageAttempt',
    ACTIVATE_CARD => 'ActivateCard',
    COUNTER_STORAGE => 'CounterStorageAction',
    TOSS_TOKEN => 'TossToken',
    DISTRACTION => 'Distraction',
    REPLAY => 'Replay',
  ];

  public static function get($actionId, &$ctx = null)
  {
    if (!in_array($actionId, self::$classes)) {
      throw new \BgaVisibleSystemException('Trying to get an atomic action not defined in Actions.php : ' . $actionId);
    }
    $name = '\Bga\Games\Catatac\Actions\\' . $actionId;
    return new $name($ctx);
  }

  public static function isDoable($actionId, $ctx, $player)
  {
    $res = self::get($actionId, $ctx)->isDoable($player);
    return $res;
  }

  public static function getErrorMessage($actionId)
  {
    $actionId = Utils::ucfirst(mb_strtolower($actionId));
    $msg = sprintf(
      'Attempting to take an action (%s) that is not possible. Either another card erroneously flagged this action as possible, or this action was possible until another card interfered.',
      $actionId
    );
    return $msg;
  }

  public static function getState($actionId, $ctx)
  {
    return self::get($actionId, $ctx)->getState();
  }

  public static function getArgs($actionId, $ctx)
  {
    $action = self::get($actionId, $ctx);
    return array_merge($action->getArgs(), ['optionalAction' => $ctx->isOptional()]);
  }

  public static function takeAction($actionId, $actionName, $args, &$ctx, $automatic = false)
  {
    $player = self::getPlayer($ctx);
    if (!self::isDoable($actionId, $ctx, $player)) {
      throw new \BgaUserException(self::getErrorMessage($actionId) . ' / ' . $actionName . ' / ' . var_export($args, true));
    }

    // Check action
    if (!$automatic) {
      Game::get()->checkAction($actionName);
      $stepId = Log::step();
      Notifications::newUndoableStep($player, $stepId);
    }

    // Run action
    $action = self::get($actionId, $ctx);
    $methodName = $actionName; //'act' . self::$classes[$actionId];
    $checkpoint = $action->$methodName(...$args);

    // Resolve action
    $checkpoint = $checkpoint ?? $ctx->isIrreversible($player);
    $ctx = $action->getCtx();
    Engine::resolveAction(['actionName' => $actionName, 'args' => $args], $checkpoint, $ctx, $automatic);
    Engine::proceed();
  }

  public static function getPlayer($node)
  {
    return Players::get($node->getRoot()->getPId());
  }

  public static function stAction($actionId, $ctx)
  {
    $player = self::getPlayer($ctx);
    if (!self::isDoable($actionId, $ctx, $player)) {
      if (!$ctx->isOptional()) {
        if (self::isDoable($actionId, $ctx, $player, true)) {
          Game::get()->gamestate->jumpToState(ST_IMPOSSIBLE_MANDATORY_ACTION);
          return;
        } else {
          throw new \BgaUserException(self::getErrorMessage($actionId));
        }
      } else {
        // Auto pass if optional and not doable
        Game::get()->actPassOptionalAction(true);
        return true;
      }
    }

    $action = self::get($actionId, $ctx);
    $methodName = 'st' . $action->getClassName();
    if (\method_exists($action, $methodName)) {
      $result = $action->$methodName();
      if (!is_null($result)) {
        if ($result === true || $result === false) {
          Engine::resolveAction([], $result, $ctx, true);
          Engine::proceed();
          return true;
        }

        $actionName = 'act' . $action->getClassName();
        self::takeAction($actionId, $actionName, $result, $ctx, true);
        return true; // We are changing state
      }
    }
  }

  public static function stPreAction($actionId, $ctx)
  {
    $action = self::get($actionId, $ctx);
    $methodName = 'stPre' . $action->getClassName();
    if (\method_exists($action, $methodName)) {
      $action->$methodName();
      $player = self::getPlayer($ctx);
      if ($ctx->isIrreversible($player)) {
        Engine::checkpoint($player->getId());
      }
    }
  }

  public static function pass($actionId, $ctx, $auto = false)
  {
    if (!$ctx->isOptional()) {
      var_dump($ctx->toArray());
      throw new \BgaVisibleSystemException('This action is not optional, ' . $actionId);
    }

    $player = self::getPlayer($ctx);
    // Check action
    if (!$auto) {
      Game::get()->checkAction('actPassOptionalAction');
      $stepId = Log::step();
      Notifications::newUndoableStep($player, $stepId);
    }

    $action = self::get($actionId, $ctx);
    $methodName = 'actPass' . $action->getClassName();
    if (\method_exists($action, $methodName)) {
      $action->$methodName();
    } else {
      Engine::resolveAction(PASS, false, $ctx, $auto);
    }
    Engine::proceed();
  }
}
