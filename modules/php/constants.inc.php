<?php

/*
 * Game options
 */


/*
 * User preferences
 */
const OPTION_CONFIRM = 103;
const OPTION_CONFIRM_DISABLED = 0;
const OPTION_CONFIRM_ENABLED = 2;
const OPTION_CONFIRM_TIMER = 3;


/*
 * State constants
 */

const GAME = 'game';
const MULTI = 'multipleactiveplayer';
const PRIVATESTATE = 'private';
const END_TURN = 'endTurn';
const ACTIVE_PLAYER = 'activeplayer';

const ST_GAME_SETUP = 1;
const ST_SETUP_BRANCH = 2;

// Setup
const ST_SETUP_DEBUG = 3;

const ST_NEW_ROUND = 10;
const ST_START_TURN = 11;
const ST_END_TURN = 12;

// Atomic actions
const ST_CHOOSE_CARD = 30;

// Engine state
const ST_GENERIC_AUTOMATIC = 88;
const ST_RESOLVE_STACK = 90;
const ST_RESOLVE_CHOICE = 91;
const ST_IMPOSSIBLE_MANDATORY_ACTION = 92;
const ST_CONFIRM_TURN = 93;
const ST_CONFIRM_PARTIAL_TURN = 94;

const ST_GENERIC_NEXT_PLAYER = 97;
const ST_END_SCENARIO = 86;
const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;

/*
 * ENGINE
 */
const NODE_SEQ = 'seq';
const NODE_OR = 'or';
const NODE_XOR = 'xor';
const NODE_PARALLEL = 'parallel';
const NODE_LEAF = 'leaf';

const ZOMBIE = 98;
const PASS = 99;

/*
 * Atomic action
 */

const CHOOSE_CARD = 'ChooseCard';

/*
 * MISC
 */

const TOKEN = 'token';
const BLACK_SIDE = 0;
const WHITE_SIDE = 1;

const BASIC = 'Basic';
const CHARGE = 'Charge';
const SPRINT = 'Sprint';
const STOP = 'Stop';
const CAPTURE = 'Capture';
const SMASH = 'Smash';
const COURAGE = 'Courage';
const IMPACT = 'Impact';
const MIRACLE = 'Miracle';
const BINGO = 'Bingo';
const FIDO = 'Fido';
const JOKER = 'Joker';


const WHITE_HIDEOUT = 0;
const WHITE_STREET = 1;
const NEUTRAL_STREET = 2;
const BLACK_STREET = 3;
const BLACK_HIDEOUT = 4;

/******************
 ****** STATS ******
 ******************/

const STAT_TURNS = 10;
const STAT_POSITION = 11;
