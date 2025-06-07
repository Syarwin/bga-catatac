<?php

namespace Bga\Games\Catatac\Models;

use Bga\Games\Catatac\Board;
use Bga\Games\Catatac\Core\Stats;
use Bga\Games\Catatac\Game;
use Bga\Games\Catatac\Helpers\Collection;
use Bga\Games\Catatac\Managers\Actions;
use Bga\Games\Catatac\Helpers\Utils;
use Bga\Games\Catatac\Managers\Cards;
use Bga\Games\Catatac\Managers\Meeples;
use Bga\Games\Catatac\Managers\Players;
use Bga\Games\Catatac\Managers\Tiles;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \Bga\Games\Catatac\Helpers\DB_Model
{
  protected string $table = 'player';
  protected string $primary = 'player_id';
  protected array $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
    'team' => ['team', 'int'],
  ];
  protected int $id;
  protected int $team;

  public function getUiData(?int $pId = null)
  {
    $datas = parent::getUiData();
    if ($pId == $this->id) {
      $datas['hand'] = $this->getHand()->toArray();
    }
    $datas['handCount'] = $this->getHand()->count();
    return $datas;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getPref(int $prefId)
  {
    return Game::get()->getGameUserPreference($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . Utils::ucfirst($name);
    return Stats::$name($this->id);
  }

  public function canTakeAction($action, $ctx)
  {
    return Actions::isDoable($action, $ctx, $this);
  }

  public function getHand(): Collection
  {
    return Cards::getInLocation("hand-$this->id")->orderBy('state', 'ASC');
  }

  public function getHideoutLocation(): int
  {
    return $this->team == 0 ? BLACK_HIDEOUT : WHITE_HIDEOUT;
  }

  public function getStreetLocation(): int
  {
    return $this->team == 0 ? BLACK_STREET : WHITE_STREET;
  }

  public function isOwningTheBall(): bool
  {
    return Meeples::getBall()->isOwned($this);
  }

  public function getTeamMembers(): Collection
  {
    return Players::getAll()->filter(fn($player) => $player->getTeam() == $this->getTeam());
  }
}
