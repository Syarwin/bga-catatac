define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  }

  const ballLocationMap = ['white-hideout', 'white-street', 'neutral-street', 'black-street', 'black-hideout'];

  return declare('catatac.meeples', null, {
    setupMeeples() {
      // This function is refreshUI compatible
      let meepleIds = this.gamedatas.meeples.map((meeple) => {
        if (!$(`meeple-${meeple.id}`)) {
          this.addMeeple(meeple);
        }

        let o = $(`meeple-${meeple.id}`);
        if (!o) return null;

        let container = this.getMeepleContainer(meeple);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
        }
        o.dataset.state = meeple.state;

        return meeple.id;
      });
      document.querySelectorAll('.catatac-meeple[id^="meeple-"]').forEach((oMeeple) => {
        if (!meepleIds.includes(parseInt(oMeeple.getAttribute('data-id'))) && oMeeple.dataset.type != 'cylinder') {
          this.destroy(oMeeple);
        }
      });
    },

    addMeeple(meeple, location = null) {
      if ($('meeple-' + meeple.id)) return;

      let o = this.place('tplMeeple', meeple, location == null ? this.getMeepleContainer(meeple) : location);
      let tooltipDesc = this.getMeepleTooltip(meeple);
      if (tooltipDesc != null) {
        this.addCustomTooltip(o.id, tooltipDesc.map((t) => this.formatString(t)).join('<br/>'));
      }

      return o;
    },

    getMeepleTooltip(meeple) {
      let type = meeple.type;
      return null;
    },

    tplMeeple(meeple) {
      let type = meeple.type.charAt(0).toLowerCase() + meeple.type.substr(1);

      if (type == 'token') {
        return `<div class="catatac-meeple" id="meeple-${meeple.id}" data-id="${meeple.id}" data-type="${meeple.type}" data-state="${meeple.state}">
        <div class="catatac-meeple-inner">
          <div class="catatac-meeple-front"></div>
          <div class="catatac-meeple-back"></div>  
        </div>
      </div>`;
      }

      return `<div class="catatac-meeple catatac-icon icon-${type}" id="meeple-${meeple.id}" data-id="${meeple.id}" data-type="${type}" data-state="${meeple.state}"></div>`;
    },

    getMeepleContainer(meeple) {
      let t = meeple.location.split('-');

      if (meeple.type == 'token') {
        return $(ballLocationMap[meeple.location]);
      }
      if ($(meeple.location)) {
        return $(meeple.location);
      }

      console.error('Trying to get container of a meeple', meeple);
      return 'game_play_area';
    },

    async notif_moveBall(args) {
      await this.slide('meeple-1', $(ballLocationMap[args.location]));
    },

    async notif_stealBall(args) {
      $('meeple-1').dataset.state = args.side;
      await this.wait(800);
    },

    async notif_tossBall(args) {
      debug('Notif: toss the yarn ball', args);
      let inner = $('meeple-1').querySelector('.catatac-meeple-inner');
      inner.style.transform = 'translateY(-150%) rotateY(1000deg)';
      await this.wait(600);
      inner.style.transform = null;
      $('meeple-1').dataset.state = args.ball.state;
      await this.wait(600);
    },

    async notif_postStorageFlip(args) {
      debug('Notif: postStorageFlip', args);
      await this.notif_tossBall(args);
      await this.slide('meeple-1', $(ballLocationMap[args.ball.location]));
    },

    onEnteringStateMoveToken(args) {
      args.locations.forEach((location) => {
        this.onClick(ballLocationMap[location], () => this.takeAtomicAction('actMoveToken', [location]));
      });
    },
  });
});
