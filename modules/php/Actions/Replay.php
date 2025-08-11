<?php

namespace Bga\Games\Catatac\Actions;

use Bga\Games\Catatac\Core\Notifications;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Models\Player;

class Replay extends \Bga\Games\Catatac\Models\Action
{
  public function getState(): int
  {
    return ST_GENERIC_AUTOMATIC;
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function isOptional(): bool
  {
    return true;
  }

  public function getDescription(): string|array
  {
    return clienttranslate("Play another card");
  }

  public function stReplay()
  {
    return [];
  }

  public function isDoable(Player $player): bool
  {
    $ball = Meeples::getBall();
    return !in_array($ball->getLocation(), [WHITE_HIDEOUT, BLACK_HIDEOUT]);
  }

  public function actReplay()
  {
    $player = $this->getPlayer();
    Notifications::message(clienttranslate('${player_name} may play another card'), ['player' => $player]);

    $this->insertAsChild([
      'action' => CHOOSE_CARD,
    ]);
  }
}
