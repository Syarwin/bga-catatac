/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Catatac implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * catatac.js
 *
 * Catatac user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  'dojo',
  'dojo/_base/declare',
  'ebg/core/gamegui',
  'ebg/counter',
  g_gamethemeurl + 'modules/js/Core/game.js',
  g_gamethemeurl + 'modules/js/Core/modal.js',
  g_gamethemeurl + 'modules/js/Players.js',
  g_gamethemeurl + 'modules/js/Cards.js',
  g_gamethemeurl + 'modules/js/Meeples.js',
], function (dojo, declare) {
  return declare('bgagame.catatac', [customgame.game, catatac.players, catatac.cards, catatac.meeples], {
    constructor() {
      this._inactiveStates = [];
      this._notifications = [
        'clearTurn',
        'refreshUI',
        'refreshHand',
        'midmessage',
        'discardCard',
        'drawCards',
        'pDrawCards',
        'moveBall',
        'stealBall',
        'tossBall',
        'storage',
        'postStorageFlip',
        'revealPoints',
      ];

      this._discardModal = null;
    },

    async notif_midmessage(args) {
      await this.wait(args.timer || 1000);
    },

    getSettingsSections() {
      return {};
    },

    getSettingsConfig() {
      return {};
    },

    /**
     * Setup:
     *	This method set up the game user interface according to current game situation specified in parameters
     *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
     *
     * Params :
     *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
     */
    setup(gamedatas) {
      debug('SETUP', gamedatas);

      this.setupInfoPanel();
      this.setupCentralArea();
      this.setupPlayers();
      this.setupCards();
      this.updateHandCards();
      this.setupMeeples();
      this.setupDiscardModal();
      this.inherited(arguments);
    },

    setupCentralArea() {
      $('game_play_area').insertAdjacentHTML(
        'beforeend',
        `
<div id="catatac-main-container">
  <div id="catatac-cards-wrapper">
    <div id="catatac-deck-discard-wrapper">
      <div class="card-slot" id="catatac-points-deck"></div>
      <div class="card-slot" id="catatac-deck"></div>
      <div class="card-slot" id="catatac-discard-holder">
        <div id="catatac-discard"></div>
      </div>
    </div>
  </div>
  <div id="catatac-board">
    <div id="catatac-board-background">
      <div id="catatac-board-background-inner">
        <div id="catatac-board-background-day"></div>
        <div id="catatac-board-background-night"></div>
      </div>
    </div>
    <div class="board-slot" id="white-points"></div>
    <div class="board-slot" id="white-hideout"></div>
    <div class="board-slot" id="white-street"></div>
    <div class="board-slot" id="neutral-street"></div>
    <div class="board-slot" id="black-street"></div>
    <div class="board-slot" id="black-hideout"></div>
    <div class="board-slot" id="black-points"></div>
  </div>
  <div id="points-reveal">
    <div id="white-points-reveal"></div>
    <div id="black-points-reveal"></div>
  </div>
  <div id="catatac-hand"></div>
</div>

<svg style="display:none" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-question" role="img" xmlns="http://www.w3.org/2000/svg">
  <symbol id="help-marker-svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="white" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="1"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g>
  </symbol>
</svg>
`
      );
    },

    // Generic automatic updating of infos
    updateInfosFromNotif(infos) {
      //   // Income
      //   if (infos.income) {
      //     Object.entries(infos.income).forEach(([pId, income]) => {
      //       this._playerCounters[pId]['income'].toValue(income);
      //     });
      //   }
      //   // Score
      //   if (infos.score) {
      //     Object.entries(infos.score).forEach(([pId, score]) => {
      //       this._scoreCounters[pId].toValue(score);
      //     });
      //   }
    },

    onScreenWidthChange() {
      if (this.settings) this.updateLayout();
    },

    /////////////////////////////////////////////////////////////////
    //  _____       _             ___
    // | ____|_ __ | |_ ___ _ __ / / |    ___  __ ___   _____
    // |  _| | '_ \| __/ _ \ '__/ /| |   / _ \/ _` \ \ / / _ \
    // | |___| | | | ||  __/ | / / | |__|  __/ (_| |\ V /  __/
    // |_____|_| |_|\__\___|_|/_/  |_____\___|\__,_| \_/ \___|
    /////////////////////////////////////////////////////////////////

    clearPossible() {
      let toRemove = [];
      toRemove.forEach((eltId) => {
        if ($(eltId)) $(eltId).remove();
      });

      this.inherited(arguments);
    },

    onUpdateActionButtons(stateName, args) {
      //        this.addPrimaryActionButton('test', 'test', () => this.testNotif());
      this.inherited(arguments);
    },

    testNotif() {},

    onEnteringState(stateName, args) {
      debug('Entering state: ' + stateName, args);
      if (this.isFastMode() && ![].includes(stateName)) return;
      $('ebd-body').dataset.state = stateName;

      if (args.args && args.args.descSuffix) {
        this.changePageTitle(args.args.descSuffix);
      }
      if (args.args && args.args.description && args.args.descriptionmyturn) {
        this.gamedatas.gamestate.descriptionmyturn = args.args.descriptionmyturn;
        this.gamedatas.gamestate.description = args.args.description;
        this.updatePageTitle();
      }

      if (args.args && args.args.optionalAction) {
        let base = args.args.descSuffix ? args.args.descSuffix : '';
        this.changePageTitle(base + 'skippable');
      }

      if (!this._inactiveStates.includes(stateName) && !this.isCurrentPlayerActive()) return;

      if (args.args && args.args.optionalAction && !args.args.automaticAction) {
        this.addSecondaryActionButton(
          'btnPassAction',
          _('Pass'),
          () => this.takeAction('actPassOptionalAction'),
          'restartAction'
        );
      }

      // Undo last steps
      if (args.args && args.args.previousSteps) {
        args.args.previousSteps.forEach((stepId) => {
          let logEntry = $('logs').querySelector(`.log.notif_newUndoableStep[data-step="${stepId}"]`);
          if (logEntry) this.onClick(logEntry, () => this.undoToStep(stepId));

          logEntry = document.querySelector(`.chatwindowlogs_zone .log.notif_newUndoableStep[data-step="${stepId}"]`);
          if (logEntry) this.onClick(logEntry, () => this.undoToStep(stepId));
        });
      }

      // Restart turn button
      if (args.args && args.args.previousEngineChoices && args.args.previousEngineChoices >= 1 && !args.args.automaticAction) {
        if (args.args && args.args.previousSteps) {
          let lastStep = Math.max(...args.args.previousSteps);
          if (lastStep > 0)
            this.addDangerActionButton('btnUndoLastStep', _('Undo last step'), () => this.undoToStep(lastStep), 'restartAction');
        }

        // Restart whole turn
        this.addDangerActionButton(
          'btnRestartTurn',
          _('Restart turn'),
          () => {
            this.stopActionTimer();
            this.takeAction('actRestart');
          },
          'restartAction'
        );
      }

      // Call appropriate method
      var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },

    /////////////////////////////
    //  _   _           _
    // | | | |_ __   __| | ___
    // | | | | '_ \ / _` |/ _ \
    // | |_| | | | | (_| | (_) |
    //  \___/|_| |_|\__,_|\___/
    /////////////////////////////

    onAddingNewUndoableStepToLog(notif) {
      if (!$(`log_${notif.logId}`)) return;
      let stepId = notif.msg.args.stepId;
      $(`log_${notif.logId}`).dataset.step = stepId;
      if ($(`dockedlog_${notif.mobileLogId}`)) $(`dockedlog_${notif.mobileLogId}`).dataset.step = stepId;

      if (
        this.gamedatas &&
        this.gamedatas.gamestate &&
        this.gamedatas.gamestate.args &&
        this.gamedatas.gamestate.args.previousSteps &&
        this.gamedatas.gamestate.args.previousSteps.includes(parseInt(stepId))
      ) {
        this.onClick($(`log_${notif.logId}`), () => this.undoToStep(stepId));

        if ($(`dockedlog_${notif.mobileLogId}`)) this.onClick($(`dockedlog_${notif.mobileLogId}`), () => this.undoToStep(stepId));
      }
    },

    undoToStep(stepId) {
      this.stopActionTimer();
      this.checkAction('actRestart');
      this.takeAction('actUndoToStep', { stepId }, false);
    },

    notif_clearTurn(args) {
      debug('Notif: restarting turn', args);
      this.cancelLogs(args.notifIds);
    },

    notif_refreshUI(args) {
      debug('Notif: refreshing UI', args);

      ['meeples', 'players', 'cards', 'tiles'].forEach((value) => {
        this.gamedatas[value] = args.datas[value];
      });
      this.setupCards();
      this.setupMeeples();
      this.updatePlayersCounters();
    },

    notif_refreshHand(args) {
      debug('Notif: refreshing UI Hand', args);
      this.gamedatas.players[args.player_id].hand = args.hand;
      this.updateHandCards();
    },

    ////////////////////////////////////////
    //  _____             _
    // | ____|_ __   __ _(_)_ __   ___
    // |  _| | '_ \ / _` | | '_ \ / _ \
    // | |___| | | | (_| | | | | |  __/
    // |_____|_| |_|\__, |_|_| |_|\___|
    //              |___/
    ////////////////////////////////////////

    addActionChoiceBtn(choice, disabled = false) {
      if ($('btnChoice' + choice.id)) return;

      let desc = this.translate(choice.description);
      desc = this.formatString(desc);

      this.addSecondaryActionButton(
        'btnChoice' + choice.id,
        desc,
        disabled
          ? () => {}
          : () => {
              this.askConfirmation(choice.irreversibleAction, () =>
                this.takeAction('actChooseAction', {
                  choiceId: choice.id,
                })
              );
            }
      );
      if (disabled) {
        $(`btnChoice${choice.id}`).classList.add('disabled');
      }
      if (choice.description.args && choice.description.args.bonus_pentagon) {
        $(`btnChoice${choice.id}`).classList.add('withbonus');
      }
    },

    onEnteringStateResolveChoice(args) {
      Object.values(args.choices).forEach((choice) => this.addActionChoiceBtn(choice, false));
      Object.values(args.allChoices).forEach((choice) => this.addActionChoiceBtn(choice, true));
    },

    onEnteringStateImpossibleAction(args) {
      this.addActionChoiceBtn(
        {
          choiceId: 0,
          description: args.desc,
        },
        true
      );
    },

    addConfirmTurn(args, action) {
      this.addPrimaryActionButton('btnConfirmTurn', _('Confirm'), () => {
        this.stopActionTimer();
        this.takeAction(action);
      });

      const OPTION_CONFIRM = 103;
      let n = args.previousEngineChoices;
      let timer = Math.min(10 + 2 * n, 20);
      this.startActionTimer('btnConfirmTurn', timer, this.prefs[OPTION_CONFIRM].value);
    },

    onEnteringStateConfirmTurn(args) {
      this.addConfirmTurn(args, 'actConfirmTurn');
    },

    onEnteringStateConfirmPartialTurn(args) {
      this.addConfirmTurn(args, 'actConfirmPartialTurn');
    },

    askConfirmation(warning, callback) {
      if (warning === false || this.prefs[104].value == 0) {
        callback();
      } else {
        this.confirmationDialog(warning, () => {
          callback();
        });
      }
    },

    // Generic call for Atomic Action that encode args as a JSON to be decoded by backend
    takeAtomicAction(action, args = [], warning = false) {
      if (!this.checkAction(action)) return false;

      this.askConfirmation(warning, () =>
        this.takeAction('actTakeAtomicAction', { actionName: action, actionArgs: JSON.stringify(args) }, false)
      );
    },

    ///////////////////////////////////////
    //      _        _   _
    //     / \   ___| |_(_) ___  _ __  ___
    //    / _ \ / __| __| |/ _ \| '_ \/ __|
    //   / ___ \ (__| |_| | (_) | | | \__ \
    //  /_/   \_\___|\__|_|\___/|_| |_|___/
    ///////////////////////////////////////

    onEnteringStateChooseCard(args) {
      let elements = {};
      args._private.cardIds.forEach((cardId) => (elements[cardId] = $(`card-${cardId}`)));

      this.onSelectN({
        elements,
        n: 1,
        callback: (selection) => this.takeAtomicAction('actChooseCard', [selection[0]]),
      });

      args._private.usefulCardIds.forEach((cardId) => elements[cardId].classList.add('selectable-useful'));
    },

    ////////////////////////////////////////////////////////////
    // _____                          _   _   _
    // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
    // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
    // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
    // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
    //                                                 |___/
    ////////////////////////////////////////////////////////////

    /**
     * Replace some expressions by corresponding html formating
     */
    formatIcon(name, n = null, lowerCase = true) {
      let type = lowerCase ? name.toLowerCase() : name;

      // SVG ICONS
      const glyphs = {};
      if (glyphs[type]) {
        let icon = `<i class='svgicon-${type}'>`;
        let nGlyphs = glyphs[type];
        if (nGlyphs > 1) {
          for (let i = 1; i <= nGlyphs; i++) {
            icon += `<span class="path${i}"></span>`;
          }
        }
        icon += '</i>';
        return icon;
      }

      const NO_TEXT_ICONS = [];
      let noText = NO_TEXT_ICONS.includes(name);
      let text = n == null ? '' : `<span>${n}</span>`;
      return `${noText ? text : ''}<div class="icon-container icon-container-${type}">
            <div class="catatac-icon icon-${type}">${noText ? '' : text}</div>
          </div>`;
    },

    formatString(str) {
      const ICONS = [
        '1-black',
        '2-black',
        '3-black',
        '4-black',
        '5-black',
        '6-black',
        '7-black',
        '8-black',
        'cat-white',
        'cat-black',
      ];

      ICONS.forEach((name) => {
        const regex = new RegExp('<' + name + ':([^>]+)>', 'g');
        str = str.replaceAll(regex, this.formatIcon(name, '<span>$1</span>'));
        str = str.replaceAll(new RegExp('<' + name + '>', 'g'), this.formatIcon(name));
      });
      str = str.replace(/\*\*([^\*]+)\*\*/g, '<b>$1</b>');

      return str;
    },

    /**
     * Format log strings
     *  @Override
     */
    format_string_recursive(log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;

          log = this.formatString(_(log));

          if (args.teamIcon) {
            args.teamIcon = this.formatString(args.teamIcon);
          }

          if (args.card_name) {
            args.card_name = `<span class='log-catatac-card-name'>${this.translate(args.card_name)}</span>`;
          }
        }
      } catch (e) {
        console.error(log, args, 'Exception thrown', e.stack);
      }

      let str = this.inherited(arguments);
      return this.formatString(str);
    },

    //////////////////////////////////////////////////////
    //  ___        __         ____                  _
    // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
    //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
    //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
    // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
    //////////////////////////////////////////////////////

    setupInfoPanel() {
      // dojo.place(this.tplInfoPanel(), 'player_boards', 'first');
      // let chk = $('help-mode-chk');
      // dojo.connect(chk, 'onchange', () => this.toggleHelpMode(chk.checked));
      // this.addTooltip('help-mode-switch', '', _('Toggle help/safe mode.'));
      // this.updateRoundNumber();

      this._settingsModal = new customgame.modal('showSettings', {
        class: 'catatac_popin',
        closeIcon: 'fa-times',
        title: _('Settings'),
        closeAction: 'hide',
        verticalAlign: 'flex-start',
        contentsTpl: `<div id='catatac-settings'>
             <div id='catatac-settings-header'></div>
             <div id="settings-controls-container"></div>
           </div>`,
      });
    },

    //   updatePlayerOrdering() {
    //     this.inherited(arguments);
    //     dojo.place('player_board_config', 'player_boards', 'first');
    //   },

    //   async notif_newRound(args) {
    //     this.gamedatas.round = args.round;
    //     this.updateRoundNumber();
    //     await this.wait(800);
    //   },

    updateLayout() {
      if (!this.settings) return;
      const ROOT = document.documentElement;
    },
  });
});
