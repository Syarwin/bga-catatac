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
    ];

    return [
      'log' => $logs[$action] ?? 'TODO ${number}',
      'args' => [
        'number' => "<$number-black>",
      ]
    ];
  }

  public function getActionBloc(): array
  {
    return [];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return false;
  }
}
