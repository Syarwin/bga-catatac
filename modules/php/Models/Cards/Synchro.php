<?php

namespace Bga\Games\Catatac\Models\Cards;

use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\PawnCard;
use Bga\Games\Catatac\Models\Player;

class Synchro extends PawnCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->tooltip = [
      clienttranslate('**Condition:** Opponent owns the yarn ball.'),
      clienttranslate('**Effects:** Counter a hoarding attempt. Play again')
    ];
  }
  public function getName(): string|array
  {
    return clienttranslate('Challenge');
  }

  public function getActionBloc(Player $player): array
  {
    return [
      'childs' => [
        [
          'action' => COUNTER_STORAGE,
        ],
        [
          'action' => REPLAY
        ]
      ]
    ];
  }

  public function canUseActionBloc(Player $player): bool
  {
    return !$player->isOwningTheBall() && in_array(Meeples::getBall()->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);
  }

  public function canCounterStorage(): bool
  {
    return true;
  }
}
