const isObject = (obj) => {
  return typeof obj === 'object' && obj !== null && !Array.isArray(obj);
};

define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  }

  return declare('catatac.cards', null, {
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
        if (!cardIds.includes(parseInt(oCard.getAttribute('data-id'))) && oCard.parentNode.id != 'catatac-hand') {
          this.destroy(oCard);
        }
      });

      $('catatac-deck').dataset.n = this.gamedatas.deckCount;
      $('catatac-points-deck').dataset.n = this.gamedatas.pointsDeckCount;
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
      // if (o !== undefined) {
      //   this.addCustomTooltip(
      //     o.id,
      //     () => {
      //       let status = this.getCardStatus(card.id);
      //       return `<div class='zoo-card-tooltip'>
      //         ${status}
      //         ${this.tplZooCard(card, true)}
      //       </div>`;
      //     },
      //     { midSize: false }
      //   );
      // }
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
    },

    getCardContainer(card) {
      let t = card.location.split('-');
      if (t[0] == 'hand') {
        return $(`catatac-hand`);
      }
      if (card.location == 'discard') {
        return $('catatac-discard');
      }

      return $('test-cards');
    },

    tplCard(card, tooltip = false) {
      let uid = 'card-' + card.id;
      let type = card.type;

      return `<div id="${uid}" class='catatac-card' data-id='${card.id}' data-type='${type}'>
        <div class='catatac-card-wrapper'></div>
      </div>`;
    },

    tplFakeCard(card) {
      let uid = 'card-' + card.id;
      return `<div id="${uid}" class='catatac-card fake-card'>
      <div class='catatac-card-wrapper'></div>
    </div>`;
    },

    async discardCard(card, pId) {
      let o = $(`card-${card.id}`);
      if (o) {
        await this.slide(`card-${card.id}`, $('catatac-discard'));
        this._playerCounters[pId]['handCount'].incValue(-1);
      }
      // Opponents => slide the tile and cards from player panels
      else {
        const playerPanel = $(`overall_player_board_${pId}`);
        this.addCard(card, playerPanel);
        await this.slide(`card-${card.id}`, $('catatac-discard'));
        this._playerCounters[pId]['handCount'].incValue(-1);
      }
    },

    async notif_discardCard(args) {
      debug('Notif: discarding card', args);
      await this.discardCard(args.card, args.player_id);
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
  });
});
