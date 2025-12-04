const isObject = (obj) => {
  return typeof obj === 'object' && obj !== null && !Array.isArray(obj);
};

define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  }

  return declare('catatac.cards', null, {
    setupDiscardModal() {
      this._discardModal = new customgame.modal('discardDisplay', {
        class: 'catatac_popin',
        autoShow: false,
        closeIcon: 'fa-times',
        closeAction: 'hide',
        title: _('Discard'),
        verticalAlign: 'flex-start',
        contentsTpl: `<div class='discard-modal' id='discard-cards'></div>`,
        scale: 0.9,
        breakpoint: 800,
        onStartShow: () => {
          this.closeCurrentTooltip();
          $(`discard-cards`).insertAdjacentElement('beforeend', $(`catatac-discard`));
        },
        onStartHide: () => {
          this.closeCurrentTooltip();
          $(`catatac-discard-holder`).insertAdjacentElement('beforeend', $(`catatac-discard`));
        },
        onShow: () => this.closeCurrentTooltip(),
      });
      $(`catatac-discard`).addEventListener('click', () => {
        this.closeCurrentTooltip();
        if (this._discardModal.isDisplayed()) this._discardModal.hide();
        else this._discardModal.show();
      });
    },

    setupCards() {
      // This function is refreshUI compatible
      let cardIds = this.gamedatas.cards.map((card) => {
        if (!$(`card-${card.id}`)) {
          this.addCard(card);
        }

        let o = $(`card-${card.id}`);
        if (!o) return null;

        let container = this.getCardContainer(card);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
        }

        return card.id;
      });
      document.querySelectorAll('.catatac-card').forEach((oCard) => {
        if (
          !cardIds.includes(parseInt(oCard.getAttribute('data-id'))) &&
          oCard.parentNode.id != 'catatac-hand' &&
          oCard.parentNode.id != 'catatac-alpha'
        ) {
          this.destroy(oCard);
        }
      });

      $('catatac-deck').dataset.n = this.gamedatas.deckCount;
      $('catatac-points-deck').dataset.n = this.gamedatas.pointsDeckCount;

      $('black-points').dataset.n = this.gamedatas.ownedPointsCards[0];
      $('white-points').dataset.n = this.gamedatas.ownedPointsCards[1];
    },

    addCard(card, container = null) {
      if (card.fake) {
        this.place('tplFakeCard', card, container);
        return;
      }

      if (container == null) {
        container = this.getCardContainer(card);
      }

      let o = this.place('tplCard', card, container);
      if (o !== undefined) {
        let tooltip = `<div class='card-tooltip'>
          <h2>${this.translate(card.name)}</h2> 
          <div class='card-desc'>${card.tooltip.map((t) => this.formatString(_(t))).join('<br />')}</div>
        </div>`;
        this.addCustomTooltip(o.id, tooltip);
      }
    },

    updateHandCards() {
      if (this.isSpectator) return;
      this.empty(`catatac-hand`);
      let hand = this.gamedatas.players[this.player_id].hand;
      hand.forEach((card) => {
        if ($(`card-${card.id}`)) {
          $(`catatac-hand`).insertAdjacentElement('beforeend', $(`card-${card.id}`));
        } else {
          this.addCard(card);
        }
      });

      this.empty(`catatac-alpha`);
      let alpha = this.gamedatas.players[this.player_id].alpha;
      if (alpha) {
        if ($(`card-${alpha.id}`)) {
          $(`catatac-alpha`).insertAdjacentElement('beforeend', $(`card-${alpha.id}`));
        } else {
          this.addCard(alpha);
        }
      }
    },

    getCardContainer(card) {
      let t = card.location.split('-');
      if (t[0] == 'alpha') {
        return $(`catatac-alpha`);
      }
      if (t[0] == 'hand') {
        return $(`catatac-hand`);
      }
      if (t[0] == 'revealed') {
        return t[1] == 0 ? $('black-points-reveal') : $('white-points-reveal');
      }
      if (card.location == 'discard') {
        return $('catatac-discard');
      }

      return $('test-cards');
    },

    tplCard(card, tooltip = false) {
      let uid = 'card-' + card.id;
      let type = card.type;
      if (type == 'AlphaWhite-Bengail' || type == 'AlphaBlack-Bengail') type = 'Alpha-Bengail';
      if (type == 'AlphaWhite-Ragdoll' || type == 'AlphaBlack-Ragdoll') type = 'Alpha-Ragdoll';

      return `<div id="${uid}" class='catatac-card' data-id='${card.id}' data-type='${type}'>
        <div class='catatac-card-wrapper'></div>
      </div>`;
    },

    tplFakeCard(card) {
      let uid = 'card-' + card.id;
      return `<div id="${uid}" class='catatac-card fake-card ${card.points ? 'points' : ''}'>
      <div class='catatac-card-wrapper'></div>
    </div>`;
    },

    async discardCard(card, pId, isAlpha = false) {
      let counter = isAlpha ? 'alphaCount' : 'handCount';
      let o = $(`card-${card.id}`);
      if (o) {
        await this.slide(`card-${card.id}`, $('catatac-discard'));
        this._playerCounters[pId][counter].incValue(-1);
      }
      // Opponents => slide the tile and cards from player panels
      else {
        const playerPanel = $(`overall_player_board_${pId}`);
        this.addCard(card, playerPanel);
        await this.slide(`card-${card.id}`, $('catatac-discard'));
        this._playerCounters[pId][counter].incValue(-1);
      }
    },

    async notif_discardCard(args) {
      debug('Notif: discarding card', args);
      await this.discardCard(args.card, args.player_id, args.alpha);

      if (args.flippedBoard !== undefined) {
        this.gamedatas.flippedBoard = args.flippedBoard;
        this.updateBoardSide();
      }
    },

    async notif_discardCardTeammate(args) {
      debug('Notif: discarding card from a teammate', args);
      await this.discardCard(args.card, args.player_id2);
    },

    /**
     * Private notification for the player drawing the card :
     *  create the cards and slide them in hand
     */
    async notif_pDrawCards(args) {
      debug('Notif: private drawing cards', args);
      let counter = 'handCount';

      if (this.isFastMode()) {
        args.cards.forEach((card) => {
          this.addCard(card);
        });
        this._playerCounters[this.player_id][counter].incValue(args.cards.length);
        $('catatac-deck').dataset.n = +$('catatac-deck').dataset.n - args.cards.length;
        return;
      }

      await Promise.all(
        args.cards.map((card, i) => {
          return this.wait(100 * i).then(() => {
            this.addCard(card);

            let to = null;
            let container = this.getCardContainer(card);
            if (!isVisible(container)) to = $('floating-hand-button');
            let source = $('catatac-deck');

            return this.slide(`card-${card.id}`, container, {
              from: source,
              duration: 1000,
              to,
            });
          });
        })
      );

      this._playerCounters[this.player_id][counter].incValue(args.cards.length);
      $('catatac-deck').dataset.n = +$('catatac-deck').dataset.n - args.cards.length;
    },

    /**
     * Public notification when drawing cards:
     *  ignore if current player is the one drawing card
     *  slide fakes cards from titlebar to player panel and increase hand count
     */
    async notif_drawCards(args) {
      debug('Notif: public drawing cards', args);
      let counter = 'handCount';

      let nCards = args.n;
      if (this.isFastMode()) {
        this._playerCounters[args.player_id][counter].incValue(nCards);
        $('catatac-deck').dataset.n = +$('catatac-deck').dataset.n - nCards;
        return;
      }

      await Promise.all(
        Array.from(Array(nCards), (x, i) => i).map((i) => {
          return this.wait(100 * i).then(() => {
            this.addCard({ id: i, fake: true }, $('catatac-deck'));
            return this.slide(`card-${i}`, `counter-${args.player_id}-${counter}`, {
              duration: 1000,
              destroy: true,
              phantom: false,
            });
          });
        })
      );

      this._playerCounters[args.player_id][counter].incValue(nCards);
      $('catatac-deck').dataset.n = +$('catatac-deck').dataset.n - nCards;
    },

    async notif_storage(args) {
      debug('Notif: storage success', args);

      let container = $(args.team == 0 ? 'black-points' : 'white-points');
      $('catatac-points-deck').dataset.n = +$('catatac-points-deck').dataset.n - 1;

      if (this.isFastMode()) {
        container.dataset.n = (+container.dataset.n || 0) + 1;
        return;
      }

      this.addCard({ id: 0, fake: true, points: true }, $('catatac-points-deck'));
      await this.slide(`card-${0}`, container, {
        duration: 1000,
        destroy: true,
        phantom: false,
      });

      container.dataset.n = (+container.dataset.n || 0) + 1;
    },

    async notif_revealPoints(args) {
      debug('Notif: reveal points', args);

      if (this.isFastMode()) {
        args.cards.forEach((card) => {
          this.addCard(card);
        });
        return;
      }

      await Promise.all(
        args.cards.map((card, i) => {
          return this.wait(100 * i).then(() => {
            this.addCard(card);

            let container = this.getCardContainer(card);
            let source = args.team == 0 ? $('black-points') : $('white-points');

            return this.slide(`card-${card.id}`, container, {
              from: source,
              duration: 1000,
            });
          });
        })
      );

      this.orderedPlayers.forEach((player) => {
        if (player.team == args.team) this.scoreCtrl[player.id].setValue(args.score);
      });
    },
  });
});
