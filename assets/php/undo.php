<?php
require_once('functions.php');

session_start();
$all_moves  	 	= &$_SESSION['game_data']['move_history'];
$board			 	= &$_SESSION['game_data']['board'];
$return_data 	 	= [];
$changed_squares 	= [];
$icons 			 	= [
	'tower' 	 	=> '<i class="fa-solid fa-chess-rook-piece"></i>',
	'horse'	 	 	=> '<i class="fa-solid fa-chess-knight"></i>',
	'bishop' 	 	=> '<i class="fa-solid fa-chess-bishop"></i>',
	'queen' 	 	=> '<i class="fa-solid fa-chess-queen"></i>',
	'king' 		 	=> '<i class="fa-solid fa-chess-king"></i>',
	'pawn' 		 	=> '<i class="fa-solid fa-chess-pawn"></i>',
];

if (sizeof($all_moves) < 2 || $_SESSION['game_data']['status'] != 'active') {
	exit;
}

$moves = array_reverse(array_splice($all_moves, -2, 2));

// create the data needed for the frond-end animations
foreach ($moves as $move) {
    $current = $move['from']['piece'];
    $before  = $move['to']['piece'];

	$return_data[] = [
		'from'  		=> $move['to']['coordinate'],
		'to'			=> $move['from']['coordinate'],
		'square_state'  => [
            'current' => generate_piece_html($current['name'], $current['team']),
            'before'  => generate_piece_html($before['name'], $before['team']),
        ],
	];

    if ($move['castling'] !== false) {
        $castling_move = $move['castling'];
        $tower = get_piece($board, $castling_move['to']);

        $return_data[] = [
            'from'  		=> cor_string($castling_move['to']),
            'to'			=> cor_string($castling_move['from']),
            'square_state'  => [
                'current' => generate_piece_html($tower['name'], $tower['team']),
                'before'  => '',
            ],
        ];
    }
}

// update the board on the backend
foreach ($moves as $move) {
    $from   = $move['from'];
    $to     = $move['to'];

    $changed_squares[] = $to['coordinate'];
    $changed_squares[] = $from['coordinate'];

    $board['squares'][$from['coordinate']]  = $from['piece'];
    $board['squares'][$to['coordinate']]    = $to['piece'];

    if ($move['castling'] !== false) {
        $castling_move      = $move['castling'];
        $changed_squares[]  = $castling_move['from'];
        $changed_squares[]  = $castling_move['to'];

        move_piece($board, $castling_move['to'], $castling_move['from'], false);
        $board['squares'][cor_string($castling_move['from'])]['has_moved'] = false;
    }
}

// reset king locations
foreach ($board['squares'] as $cor_str => $piece) {
    if ($piece['name'] == 'king') {
        $board[$piece['team'].'_king'] = create_cor($cor_str);
    }
}

update_movements($board, $changed_squares);
echo json_encode($return_data);
?>