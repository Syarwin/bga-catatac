define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('catatac.players', null, {
    getPlayers() {
      return Object.values(this.gamedatas.players);
    },

    setupPlayers() {
      // Change No so that it fits the current player order view
      let currentNo = this.getPlayers().reduce((carry, player) => (player.id == this.player_id ? player.no : carry), 0);
      let nPlayers = Object.keys(this.gamedatas.players).length;
      this.forEachPlayer((player) => (player.order = (player.no + nPlayers - currentNo) % nPlayers));
      this.orderedPlayers = Object.values(this.gamedatas.players).sort((a, b) => a.order - b.order);

      // Add player mat and player panel
      let teams = [[], []];
      this.orderedPlayers.forEach((player, i) => {
        teams[player.team].push(player.name);

        // Panels
        this.place('tplPlayerPanel', player, `overall_player_board_${player.id}`);

        if (i == 0) {
          $('catatac-main-container').insertAdjacentHTML(
            'beforeend',
            `<div id="hand-${player.id}" class="player-board-hand"></div>`
          );
        }
      });
      // this.setupPlayersCounters();

      $('catatac-board').insertAdjacentHTML(
        'beforeend',
        `<div id="team-black">${this.formatIcon('cat-black')} ${teams[0].join(', ')} ${this.formatIcon('cat-black')}</div>`
      );
      $('catatac-board').insertAdjacentHTML(
        'beforeend',
        `<div id="team-white">${this.formatIcon('cat-white')} ${teams[1].join(', ')} ${this.formatIcon('cat-white')}</div>`
      );
    },

    tplPlayerPanel(player) {
      return `<div class='player-info'>
        <div class="player-team">
          ${this.formatIcon(player.team == 0 ? 'cat-black' : 'cat-white')}
        </div>

        <div class="player-handCount">
          <span id="counter-${player.id}-handCount"></span>
          ${this.formatIcon('hand')}
        </div>
      </div>`;
    },
  });
});
