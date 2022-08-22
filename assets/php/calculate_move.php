<?php
/**
 * The script that will process the user's move and returns the chosen move from the bot
 * The request will come from a jquery post.
 */
session_start();
require_once('functions.php');

if (!isset($_SESSION['game_data'])) {
	exit('ERROR: game not set');
}
if (!isset($_SESSION['game_data']['board'])) {
	exit('ERROR: game board not set');
}
if (!isset($_REQUEST['from']) || !isset($_REQUEST['to'])) {
	exit('ERROR: no move given');
}
if ($_SESSION['game_data']['status'] != 'active') {
	exit('ERROR: game is over, pieces cant be moved anymore');
}

$board          = $_SESSION['game_data']['board'];
$from_cor_str   = $_REQUEST['from'];
$to_cor_str     = $_REQUEST['to'];
$from_cor       = create_cor($from_cor_str);
$to_cor         = create_cor($to_cor_str);
$piece          = get_piece($board, $from_cor);
$movements      = get_movements($board, $from_cor);
$num_moves      = 0;
$return_move	= null;

if ($piece['team'] != 'bottom') {
	exit('ERROR: wrong team idiot');
}
if (!in_array($to_cor, $movements['movements'])) {
	exit('ERROR: illegal movement');
}

$_SESSION['game_data']['move_history'][] = [
    'from' => [
        'coordinate' => $from_cor_str,
        'piece'      => get_piece($board, $from_cor_str),
    ],
    'to' => [
        'coordinate' => $to_cor_str,
        'piece'      => get_piece($board, $to_cor_str),
    ],

	'castling'		 => check_castling($board, $from_cor, $to_cor),
];
move_piece($board, $from_cor, $to_cor);

foreach ($board['squares'] as $piece) {
	if ($piece['team'] == 'top') {
		$num_moves += sizeof($piece['movements']);
	}
}

if ($num_moves == 0) {

	// bot checkmate
	if (is_square_attacked($board, 'top', $board['top_king'])) {
		$_SESSION['game_data']['status'] = 'checkmate';
		$_SESSION['game_data']['winner'] = $_COOKIE['name'];
	} else { // stalemate
		$_SESSION['game_data']['status'] = 'stalemate';
		$_SESSION['game_data']['winner'] = 'none';
	}

} else {

	$bot_move_data  = calculate_move($board, 'top', $_SESSION['game_data']['level']);
	$return_move	= $bot_move_data['move'];

	$_SESSION['game_data']['move_history'][] = [

        'from' => [
            'coordinate' => cor_string($return_move['from']),
            'piece'      => get_piece($board, $return_move['from']),
        ],
        'to' => [
            'coordinate' => cor_string($return_move['to']),
            'piece'      => get_piece($board, $return_move['to']),
        ],

		'castling'       => check_castling($board, $from_cor, $to_cor),
	];

	move_piece($board, $return_move['from'], $return_move['to']);

	// player checkmate
	$_SESSION['game_data']['status'] = $bot_move_data['status'];
	if ($bot_move_data['status'] == 'checkmate') {
		$_SESSION['game_data']['winner'] = $_SESSION['game_data']['bot_name'];
	} else if ($bot_move_data['status'] == 'stalemate') {// stalemate
		$_SESSION['game_data']['winner'] = 'none';
	}

    $return_move['message'] = $bot_move_data['message'];
}

$return_data = [
	'winner' => $_SESSION['game_data']['winner'],
	'status' => $_SESSION['game_data']['status'],
];

if ($return_move != null) {
	$return_data['move'] = $return_move;
}

$_SESSION['game_data']['board'] = $board;
echo json_encode($return_data);
?>