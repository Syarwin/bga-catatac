<?php

namespace Bga\Games\Catatac\Models\AlphaCards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\AlphaCard;
use Bga\Games\Catatac\Models\Player;

class Manx extends AlphaCard
{
  protected int $sardines = 1;

  public function __construct($row)
  {
    parent::__construct($row);
    $this->name = clienttranslate("Manx");
    $this->tooltip = [
      clienttranslate('**Condition:** No condition.'),
      clienttranslate('**Effects:** Move the yarn ball by 1 or 2 alleys.')
    ];
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'action' => MOVE_TOKEN,
      'args' => ['n' => [1, 2]]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return true;
  }
}
