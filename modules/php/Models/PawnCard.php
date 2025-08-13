<?php

namespace Bga\Games\Catatac\Models;

use Bga\Games\Catatac\Core\Engine;

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

    $logs = [
      'Bingo' => clienttranslate('Bingo${number}'),
      'Capture' => clienttranslate('Capture${number}'),
      'Charge' => clienttranslate('Charge${number}'),
      'Courage' => clienttranslate('Courage${number}'),
      'Fido' => clienttranslate('Fido${number}'),
      'Impact' => clienttranslate('Impact${number}'),
      'Miracle' => clienttranslate('Miracle${number}'),
      'Smash' => clienttranslate('Smash${number}'),
      'Sprint' => clienttranslate('Sprint${number}'),
      'Stop' => clienttranslate('Stop${number}'),
      'Brute' => clienttranslate('Brute${number}'),
      'Bicycle' => clienttranslate('Bicycle${number}'),
      'Dribble' => clienttranslate('Dribble${number}'),
      'Rat' => clienttranslate('Rat${number}'),
      'Zzzz' => clienttranslate('Zzzz${number}'),
      'Beurgh' => clienttranslate('Beurgh${number}'),
      'Ninja' => clienttranslate('Ninja${number}'),
      'Synchro' => clienttranslate('Synchro${number}'),
      'Duo' => clienttranslate('Duo${number}'),
      'Accident' => clienttranslate('Accident${number}'),
      'Reflexes' => clienttranslate('Reflexes${number}'),
      'Mission' => clienttranslate('Mission${number}'),
      'Schrodinger' => clienttranslate('Schrödinger${number}'),
      'Agile' => clienttranslate('Agile${number}'),
    ];

    return [
      'log' => $logs[$action] ?? 'TODO ${number}',
      'args' => [
        'number' => "<$number-black>",
      ]
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [];
  }

  public function canBePlayed(Player $player): bool
  {
    return true;
  }

  public function isAlpha(): bool
  {
    return false;
  }

  public function canUseActionBloc(Player $player): bool
  {
    return false;
  }

  public function canTakeActionBloc(Player $player): bool
  {
    if (!$this->canUseActionBloc($player)) return false;

    $flow = $this->getActionBloc($player);
    if (empty($flow)) return false;
    $tree = Engine::buildTree($flow);
    return $tree->isDoable($player);
  }

  public function canCounterStorage(): bool
  {
    return false;
  }

  public function canBePlayedCoop(): bool
  {
    return false;
  }
}
