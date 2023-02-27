<?php
/**
 * A collection of all function declarations
 */

define('BR', '<br>');

function url(string $path_from_root = ''): string
{
    /**
     * Creates a absolute path to a file or url.
     * Automatically strips the leading & trailing '/' to improve SEO
     * 
     * Read purpose here:
     * @link https://github.com/Sander-Brilman/php-website-template#how-to-use--links-on-the-webpage-important
     * 
     * @param string
     * 
     * @return string the absolute path
     */
    global $site_url;
    $path_array = explode('/', $path_from_root);

    // remove leading '/'
    if (substr($path_from_root, 0, 1) == '/') {$path_from_root = substr($path_from_root, 1);}  


    // remove trailing '/'
    if (end($path_array) === '') {
        $param_location = strpos($path_from_root, '?');
        $path_from_root = $param_location === false ? substr($path_from_root,0,strlen($path_from_root)-1) : substr($path_from_root,0,$param_location-1).substr($path_from_root,$param_location);    
    }

    return $site_url . $path_from_root;
}

function redirect(string $url_from_root, bool $use_url_function = true, bool $same_page = false): void
{
    /**
     * Redirect to a new page.
     * 
     * Used for easy redirecting.
     * 
     * @param string The path from the root website url.
     * 
     * @param bool Run the entered value through the url function.
     * @param bool Redirect to the same page (to clear POST)
     * 
     * @return void
     */
    global $site_folder;
    if ($same_page) {
        $redirect = url(str_replace($site_folder, '', $_SERVER['REQUEST_URI']));
    } else {
        $redirect = $use_url_function ? url($url_from_root) : $url_from_root;
    }
    header('location: '.$redirect);
    exit;
    return;
}

function create_form_id(string $unique_name, int $verify_code_length = 10): string
{
    /**
     * Creates a unique verify code for a form.
     * This code is used to verify that the form is from this website.
     * 
     * Set the given unique name as the 'name' attribute and the return string as the 'value' attribute of the submit button.
     * Use the check_form_id function to check if that specific form has been submitted.
     * 
     * Purpose is to prevent Cross Site Request Forgery.
     * 
     * Docs:
     * @link https://github.com/Sander-Brilman/php-website-template#security-features--cross-site-request-forgery
     * 
     * @param string The name of the form. Must be unique. Use this name to verify the form in the check_form_id function.
     * 
     * @return string The verify code of the form
     */
    $code = bin2hex(random_bytes($verify_code_length / 2));
    $_SESSION['forms'][$unique_name] = $code;
    return $code;
}

function check_form_id(string $form_name): bool
{
    /**
     * Checks if there has been a form submitted with the given name.
     * Returns true if it contains the same name & verify code
     * 
     * How to use:
     * @link https://github.com/Sander-Brilman/php-website-template#security-features--cross-site-request-forgery
     * 
     * @param string The name of the form you want to check for submission
     * 
     * @return bool
     */
    return (
        isset($_POST[$form_name]) &&
        isset($_SESSION['forms'][$form_name]) &&
        $_POST[$form_name] === $_SESSION['forms'][$form_name]
    );
}

function safe_echo(string $string): void
{
    /**
     * Easy to use function that prevents XSS.
     * 
     * Docs:
     * @link https://github.com/Sander-Brilman/php-website-template#security-features--cross-site-request-forgery
     * 
     * @param string The string to echo
     * @return void
     */
    echo htmlspecialchars($string);
}



// ------------------------------
//      chess bot functions
// ------------------------------

function create_cor($string)
{
	/**
	 * Transforms a coordinate string to a coordinate array.
     * This makes it easier to calculate movements with
	 * if the value is already an array it will be returned unchanged
	 * 
	 * @param string coordinate string 
	 * 
	 * @return array array with X and Y
	 */
	if (is_string($string)) {
		$explode = explode('-', $string);
		return [
			'x' => (int)$explode[0],
			'y' => (int)$explode[1],
		];
	} else {
		return $string;
	}
}

function cor_string($cor) {
	/**
	 * Coordinate array back to string. 
     * 
	 * @param array coordinate string 
	 * 
	 * @return string array with X and Y
	*/
    return is_array($cor) ? $cor['x'] . '-' . $cor['y'] : $cor;
}

function valid_cor(array $cor)
{
	/**
	 * Check if the coordinate is within the board limits
	 * 
	 * @param array coordinate string 
	 * 
	 * @return bool
	 */
	return ($cor['x'] > 7 || $cor['x'] < 0 || $cor['y'] > 7 || $cor['y'] < 0) ? false : true;
}

function opposite_team(string $team)
{
	/**
	 * return a string of the opposite team
	 * 
	 * @param string the team you want the opposite from
	 * 
	 * @return string
	 */
	return $team == 'top' ? 'bottom' : 'top';
}

function get_piece(array $board, $cor)
{
	/**
	 * Get the piece data from a loaction on the board
	 * 
	 * @param array the board array
	 * @param array the location you want the data from
	 * 
	 * @return array a array containing the piece information
	 */
	return $board['squares'][cor_string($cor)];
}

function place_piece(array &$board, $cor, string $piece_name, string $piece_team, bool $has_moves = true)
{
	/**
	 * Place a piece on the board.
	 * Movements are not included.
	 * 
	 * @param array the board array
	 * @param array coordinates for where to put the piece
	 * @param string king | queen | tower | bishop | horse | pawn
	 * @param string  top | bottom
	 * 
	 * @return null
	 */
	$cor = is_string($cor) ? create_cor($cor) : $cor;
	$board['squares'][cor_string($cor)] = [
		'name' 		=> 	$piece_name,
		'team' 		=> 	$piece_team,
		'has_moved' =>  $has_moves,
	];
	if ($piece_name == 'king') {
		$board[$piece_team.'_king'] = $cor;
	}
}

function remove_piece(array &$board, $cor) {
	/**
	 * Removes a piece from the board.
     * The array keys will still be there but they will be empty.
	 * 
	 * @param array the board array
	 * @param array coordinates array
	 * 
	 * @return null
	 */
	$board['squares'][cor_string($cor)] = ['name' => '', 'team' => '', 'has_moved' => true];
}

function move_piece(array &$board, $old_cor, $new_cor, bool $update_movements = true)
{
	/**
	 * Move a piece from the old coordinates to the new coordinates.
	 * Automatically updates the movements.
	 * 
	 * @param array the board array
	 * @param array/string the current location of the piece
	 * @param array/string the location where the piece needs to go
	 * @param bool if the movements of the pieces are updated.
	 * 
	 * @return void 
	 */
	$piece 				= get_piece($board, $old_cor);
	$old_cor			= create_cor($old_cor);
	$new_cor			= create_cor($new_cor);
	$changed_squares 	= [$old_cor, $new_cor];

	// code for castling
    $castling_check = check_castling($board, $old_cor, $new_cor);
    if ($castling_check !== false) {

        move_piece($board, $castling_check['from'], $castling_check['to'], false);
		$changed_squares[] = $castling_check['from'];
		$changed_squares[] = $castling_check['to'];

    }

	place_piece($board, $new_cor, $piece['name'], $piece['team']);
	remove_piece($board, $old_cor);

    // replace a pawn with a queen
    if ($piece['name'] == 'pawn' && ($new_cor['y'] == 0 || $new_cor['y'] == 7)) {
		place_piece($board, $new_cor, 'queen', $piece['team']);
	}

	if ($piece['name'] == 'king') {
		$board[$piece['team'].'_king'] = $new_cor;
	}

	if ($update_movements) {
		update_movements($board, $changed_squares);
	}

	return true;
}

function valid_move(array $board, string $team, $old_cor, $new_cor, array $king_cor = null) {
	/**
	 * Validates if a move can be made without the king being in check.
	 * Used for filtering impossible moves.
	 * 
	 * @param array the board array
	 * @param string the team of the piece
	 * @param array the location where the piece came from
	 * @param array the location where the piece needs to go
	 * @param array the location of the king
	 * 
	 * @return bool
	 */

	move_piece($board, $old_cor, $new_cor, false);

	// find king location if needed
	if ($king_cor == null || $king_cor == '') {
		$king_cor = $board[$team.'_king'];
	} else if ($old_cor == $king_cor) {
		$king_cor = $new_cor;
	}

	return !is_square_attacked($board, $team, $king_cor);
}

function is_square_attacked(array $board, string $team, array $square_cor) {
	/**
	 * Checks if a square is attacked in any possible way by the other team
	 * Used mostly to check if the king is under attack
	 * 
	 * @param array the board array
	 * @param string the team of the piece
	 * @param array the location piece is
	 * 
	 * @return bool 
	 */

	$enemy_team = opposite_team($team);
	$directions_normal = [
		['x' =>  1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' => -1, 'y' => -1, 'direction' => 'diagonal'],
		['x' =>  1, 'y' => -1, 'direction' => 'diagonal'],
		['x' => -1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' =>  0, 'y' =>  1, 'direction' => 'up-down'],
		['x' =>  0, 'y' => -1, 'direction' => 'up-down'],
		['x' =>  1, 'y' =>  0, 'direction' => 'up-down'],
		['x' => -1, 'y' =>  0, 'direction' => 'up-down'],
	];
	$directions_horse = [
		['x' =>  2, 'y' =>  1],
		['x' =>  1, 'y' =>  2],
		['x' => -2, 'y' =>  1],
		['x' =>  1, 'y' => -2],
		['x' => -1, 'y' => -2],
		['x' => -2, 'y' => -1],
		['x' => -1, 'y' =>  2],
		['x' =>  2, 'y' => -1],
	];

	// loop for diagonal, horizontal and vertical
	foreach ($directions_normal as $direction) {
		$cor = $square_cor;
		for ($squares_moved = 0; $squares_moved < 8; $squares_moved++) {

			$cor['x'] += $direction['x'];
			$cor['y'] += $direction['y'];
			if (!valid_cor($cor)) {
				break;
			}

			$piece = get_piece($board, $cor);
			if ($piece['name'] == '') {
				continue;
			} else if ($piece['team'] == $enemy_team) {

				if (
					($piece['name'] == "bishop"	&& $direction['direction']	== 'diagonal') || 
					($piece['name'] == "tower"	&& $direction['direction']	== 'up-down' ) ||
					($piece['name'] == "king"	&& $squares_moved 			== 0		 ) ||
					($piece['name'] == "queen"											 ) ||

					(($piece['name'] == "pawn" 	 && $direction['direction']	== 'diagonal' && $squares_moved == 0) &&
					(($piece['team'] == 'bottom' && $direction['y'] > 0) ||	($piece['team'] == 'top' 	&& $direction['y'] < 0)))
				) 
				{
					return true;
				}
				break;

			}
			break;
		}
	}

	// loop for horses
	foreach ($directions_horse as $direction) {
		$horse_cor = $square_cor;
		$horse_cor['x'] += $direction['x'];
		$horse_cor['y'] += $direction['y'];
		if (!valid_cor($horse_cor)) {
			continue;
		}
		$piece = get_piece($board, $horse_cor);
		if ($piece['name'] == "horse" && $piece['team'] == $enemy_team) {
			return true;
		}
	}

	return false;
}

function related_pieces(array $board, $square_cor)
{
	/**
	 * Gets all pieces that are related to the given coordinates.
	 * Uses almost the same technique as the is_square_attacked function. 
	 * 
	 * @param array the board array
	 * @param array the coordinates it gets the squares from
	 * 
	 * @return array a array with coordinates
	 */
	$squares = [];
	$directions_normal = [
		['x' =>  1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' => -1, 'y' => -1, 'direction' => 'diagonal'],
		['x' =>  1, 'y' => -1, 'direction' => 'diagonal'],
		['x' => -1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' =>  0, 'y' =>  1, 'direction' => 'up-down'],
		['x' =>  0, 'y' => -1, 'direction' => 'up-down'],
		['x' =>  1, 'y' =>  0, 'direction' => 'up-down'],
		['x' => -1, 'y' =>  0, 'direction' => 'up-down'],
	];
	$directions_horse = [
		['x' =>  2, 'y' =>  1],
		['x' =>  1, 'y' =>  2],
		['x' => -2, 'y' =>  1],
		['x' =>  1, 'y' => -2],
		['x' => -1, 'y' => -2],
		['x' => -2, 'y' => -1],
		['x' => -1, 'y' =>  2],
		['x' =>  2, 'y' => -1],
	];

	// loop for diagonal, horizontal and vertical
	foreach ($directions_normal as $direction) {
		$cor = $square_cor;
		for ($squares_moved = 0; $squares_moved < 8; $squares_moved++) {

			$cor['x'] += $direction['x'];
			$cor['y'] += $direction['y'];
			if (!valid_cor($cor)) {
				break;
			}

			$piece = get_piece($board, $cor);
			if ($piece['name'] == '' || $piece['name'] == 'horse') {
				continue;
			}
			
			if (($piece['name'] == "bishop"	&& $direction['direction'] 	== 'diagonal') || 
				($piece['name'] == "tower" 	&& $direction['direction'] 	== 'up-down' ) ||
				($piece['name'] == "king" 	&& $squares_moved 			== 0) ||
				($piece['name'] == "pawn" 	&& $squares_moved 			== 0) ||
				($piece['name'] == "queen") ||

				($piece['name'] == "pawn" 	&& $squares_moved == 1 && !$piece['has_moved'] && $direction['direction'] == 'up-down')) 
			{
				$squares[] = $cor;
			}
			break;
		}
	}

	// loop for horses
	foreach ($directions_horse as $direction) {
		$horse_cor = $square_cor;
		$horse_cor['x'] += $direction['x'];
		$horse_cor['y'] += $direction['y'];
		if (!valid_cor($horse_cor)) {
			continue;
		}

		$piece = get_piece($board, $horse_cor);
		if ($piece['name'] == "horse") {
			$squares[] = $horse_cor;
		}
	}

	return $squares;
}

function update_movements(array &$board, array $changed_squares = [])
{
	/**
	 * Calculates the movements for the pieces.
     * The $changed_squares schould contain a list of all squares that have changed value.
     * Based on that list the function will update the pieces.
	 * 
	 * @param array the board array
	 * @param array a array of the old locations of the pieces before they moved,
	 * if the array empty he will search for pieces with no movements
     * @param bool recalculate all pieces movements
	 * 
	 * @return void
	*/
	$update_pieces = [];
    $teams         = ['top', 'bottom'];

	// reset all pieces if a king is under attack or is no longer under attack
	if (is_square_attacked($board, 'top', $board['top_king']) ||
		is_square_attacked($board, 'bottom', $board['bottom_king']) ||
		$board['in_check']) {

        $board['in_check'] = !$board['in_check'];
		foreach ($board['squares'] as $cor_str => $piece) {
			$update_pieces[$cor_str] = create_cor($cor_str);
		}

	} else if (sizeof($changed_squares) == 0) {
        // select pieces with no movements key set + their related pieces

		foreach ($board['squares'] as $cor_str => $piece) {
			if ($piece['name'] != '' && !isset($piece['movements'])) {
				$cor_array = create_cor($cor_str);
				$related_pieces = related_pieces($board, $cor_array);
				foreach ($related_pieces as $related_cor) {
					$update_pieces[cor_string($related_cor)] = $related_cor;
				}
				$update_pieces[$cor_str] = $cor_array;
			}
		}

	} else {
        // select the pieces related to the changed squares

		foreach ($changed_squares as $cor) {
			$cor = create_cor($cor);
			$piece = get_piece($board, $cor);

			$related_pieces = related_pieces($board, $cor);
			foreach ($related_pieces as $related_cor) {
				$update_pieces[cor_string($related_cor)] = $related_cor;
			}
			
			$update_pieces[cor_string($cor)] = $cor;
		}

	}

    // always select both team kings to prevent unforseen bugs.
    foreach ($teams as $team) {
        $king = $board[$team.'_king'];
        $update_pieces[cor_string($king)] = $king;
    }

    // update the selected pieces
	foreach ($update_pieces as $cor_str => $cor) {
		$movements = get_movements($board, $cor);
		$piece = get_piece($board, $cor);
		
		foreach ($movements['movements'] as $index => $movement_cor) {
			if (!valid_move($board, $piece['team'], $cor, $movement_cor, $board[$piece['team'].'_king'])) {
				unset($movements['movements'][$index]);
			}
		}

		$board['squares'][$cor_str]['attack_squares'] = $movements['attack_squares'];
		$board['squares'][$cor_str]['movements'] = $movements['movements'];
	}
}

function create_board() {
	/**
	 * Create a new board with all pieces and movements set default.
	 * 
	 * @return array a new board array
	*/
	$debug = false;
	// $debug = true;

	$board = [
		'squares' => [],
		'in_check'  => false,
	];
	$teams = [
		'top' => ['pawn' => '1', 'other' => '0'],
		'bottom' => ['pawn' => '6', 'other' => '7'],
	];
	$pieces_placement = [
		'tower' => [0,7],
		'horse' => [1,6],
		'bishop' => [2,5],
		'king' => 3,
		'queen' => 4,
	];

	for ($i = 0; $i < 8; $i++) {
		for ($j = 0; $j < 8; $j++) {
			$cor_str = "$i-$j";
			place_piece($board, $cor_str, '', '', true);
		}
	}

	if ($debug) {

        // custom start senario for debugging purposes
        place_piece($board, '0-0', 'tower', 'top', true);
        place_piece($board, '0-1', 'pawn', 'top', true);
        place_piece($board, '1-2', 'pawn', 'top', true);
        place_piece($board, '1-5', 'pawn', 'bottom', true);
        place_piece($board, '2-0', 'king', 'top', true);
        place_piece($board, '2-4', 'horse', 'bottom', true);
        place_piece($board, '3-7', 'tower', 'bottom', true);
        place_piece($board, '4-1', 'pawn', 'top', true);
        place_piece($board, '4-4', 'pawn', 'top', true);
        place_piece($board, '4-6', 'king', 'bottom', true);
        place_piece($board, '5-1', 'horse', 'bottom', true);
        place_piece($board, '6-0', 'horse', 'top', true);
        place_piece($board, '6-4', 'pawn', 'bottom', true);
        place_piece($board, '6-6', 'bishop', 'top', true);
        place_piece($board, '7-2', 'pawn', 'top', true);
        place_piece($board, '7-4', 'pawn', 'bottom', true);

	} else {
		foreach ($teams as $team => $y_array) {
			// pawns
			for ($i = 0; $i < 8; $i++) {
				place_piece($board, "$i-". $y_array['pawn'], 'pawn', $team, false);
			}

			// other pieces
			foreach ($pieces_placement as $name => $cor_x) {
				if (is_array($cor_x)) {
					place_piece($board, $cor_x[0].'-'.$y_array['other'], $name, $team, false);
					place_piece($board, $cor_x[1].'-'.$y_array['other'], $name, $team, false);
				} else {
					place_piece($board, $cor_x.'-'.$y_array['other'], $name, $team, false);
				}
			}
		}
	}

	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '') {
			continue;
		}

		$cor        = create_cor($cor_str);
		$movements  = get_movements($board, $cor);

		$board['squares'][$cor_str]['movements']      = $movements['movements'];
		$board['squares'][$cor_str]['attack_squares'] = $movements['attack_squares'];
	}
	return $board;
}

function check_castling(array $board, array $from, array $to)
{
    /**
	 * Checks if a move is a castling move.
     * Is used for checking if a extra castling move is necessary
	 * 
     * @param array the board array
     * @param array coordinate array
     * @param array coordinate array
     * 
     * returns the old and the new location of the tower involved, uses from-to format.
     * returns false is there is no castling happening
	 * @return array/false
	*/
    $piece = get_piece($board, $from);
	if ($piece['name'] != 'king' || abs($from['x'] - $to['x']) != 2) {
        return false;
    }

    return [
        'from' => create_cor($from['x'] > $to['x'] ? '0-'.$from['y'] : '7-'.$from['y']),
        'to'   => create_cor($from['x'] > $to['x'] ? ($from['x'] - 1).'-'.$from['y'] : ($from['x'] + 1).'-'.$from['y']),
    ];
}

function generate_piece_html(string $name, string $team)
{
    /**
	 * Generates the html for a piece.
     * Is used for regenerating the html for the frond-end when loading the page or for the undo animations.
	 * 
     * @param string the name of the piece: pawn | horse | bishop | tower | queen | king
     * @param string the team of the piece
     * 
	 * @return string
	*/
    $icons = [
        'tower' 	=> '<i class="fa-solid fa-chess-rook-piece"></i>',
        'horse'	 	=> '<i class="fa-solid fa-chess-knight"></i>',
        'bishop' 	=> '<i class="fa-solid fa-chess-bishop"></i>',
        'queen' 	=> '<i class="fa-solid fa-chess-queen"></i>',
        'king' 		=> '<i class="fa-solid fa-chess-king"></i>',
        'pawn' 		=> '<i class="fa-solid fa-chess-pawn"></i>',
    ];

    return $name == '' ? '' : '<div data-piece="'.$name.'" class="'.$team.'">'.$icons[$name].'</div>';
}

function get_attackers_defenders(array $board) {
	/**
	 * Create a array the pieces and their already existing information.
     * All pieces also have a bonus array with locations of their attackers & defenders.
	 * Used for fast filtering of bad moves
     * 
	 * @param array board array
	 * 
	 * @return array the array with pieces
	*/
	$teams = [
		'bottom' => [],
		'top' =>    [],
	];

	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '') {
			continue;
		}
		$teams[$piece['team']][$cor_str] 				= $piece;
		$teams[$piece['team']][$cor_str]['value']		= get_value($piece['name']);
		$teams[$piece['team']][$cor_str]['attackers'] 	= [];
		$teams[$piece['team']][$cor_str]['defenders'] 	= [];
	}

	foreach ($teams as $team => $pieces_array) {
		foreach ($pieces_array as $cor_str => $piece) {

			$piece_cor = create_cor($cor_str);
			foreach ($piece['attack_squares'] as $cor) {

				$current_square = get_piece($board, $cor);
				if ($current_square['name'] == '') {
					continue;
				}

				$status = $current_square['team'] == $team ? 'defenders' : 'attackers';
				$teams[$current_square['team']][cor_string($cor)][$status][] = $piece_cor;
			}

		}
	}
	
	return $teams;
}


function board_score(array $board, string $team_turn, string $get_score_for = null, bool $full_array = false) {
	/**
	 * Calculate the score for both teams Based upon the current board.
	 * The score is calculated by these points:
	 * -> if the king is under attack
	 * -> checkmate/stalemate
	 * -> pieces that will get hit by a piece with a lower value
	 * -> total value of the pieces
	 * 
	 * the return value will be ($team_1_score - $team_2_score) unless $full_array is true,
     * In that case the full score of both teams will be returned
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param string the team that the score will be calculated for, if null it will choose $team_turn
	 * @param bool give the full score array
	 * 
	 * @return int/array
	*/
	$get_score_for = $get_score_for == null ? $team_turn : $get_score_for;
	$score = [
		'top' 		=> 0,
		'bottom' 	=> 0,
	];
	$movements_count = [
		'top' 		=> 0,
		'bottom' 	=> 0,
	];
    $kings_under_attack = [
        'top'       => false,
        'bottom'    => false,
    ];

	$king_under_attack 		= -50;
	$check_mate_value 		= -999;
	$attacked_by_piece		= -25;
	$opposite_team 			= opposite_team($team_turn);
	$end_game 				= false;

	// 
	// check if game is over
	// 
	foreach ($board['squares'] as $cor => $piece) {
		if ($piece['name'] != '') {
			$movements_count[$piece['team']] += sizeof($piece['movements']);
			$score[$piece['team']] += get_value($piece['name']);
		}
	}

	foreach ($movements_count as $team => $number_of_movements) {
		$king_attacked = is_square_attacked($board, $team, $board[$team.'_king']);
        $kings_under_attack[$team] = $king_attacked;
		if ($king_attacked) {
			$score[$team] += $king_under_attack;
		}

		if ($number_of_movements == 0) {

			if ($king_attacked) {
				$score[$team] += $check_mate_value;
			} else {
				$score = [
					'top' =>    0,
					'bottom' => 0,
				];
			}

			$end_game = true;
			break;
		}
	}

	if ($end_game) {
		$opposite_score_team = opposite_team($get_score_for);
		return $full_array ? $score : $score[$get_score_for] - $score[$opposite_score_team];
	}

	// 
	// pieces under attack by a lower value piece 
	// 
	$highest_loss = 0;
	$pieces_array = get_attackers_defenders($board);
	foreach ($pieces_array[$opposite_team] as $cor_str => $piece) {

		if (sizeof($piece['attackers']) == 0) {
			continue;
		}

		// count the half value as a loss
		if (sizeof($piece['defenders']) == 0) {
			if ($piece['value'] > $highest_loss) {
				$highest_loss = $piece['value'] / 2;
			}
			continue;            
		}

		// count the piece value - attacker value as a loss
		$lowest_attacker = null;
		foreach ($piece['attackers'] as $cor) {
			$attacker_cor_str = cor_string($cor);
			$attacker_value = get_value(get_piece($board, $attacker_cor_str)['name']);
			if ($lowest_attacker === null || $attacker_value < $lowest_attacker) {
				$lowest_attacker = $attacker_value;
			}
		}
		if ($piece['value'] > $lowest_attacker && $piece['value'] - $lowest_attacker > $highest_loss) {
			$highest_loss = $piece['value'] - $lowest_attacker;
			continue;
		}

	}
	$score[$opposite_team] -= $highest_loss;

	$danger_cases = [];
	foreach ($pieces_array[$team_turn] as $cor_str => $piece) {

		if (sizeof($piece['attackers']) == 0) {
			continue;
		}

		if (sizeof($piece['defenders']) == 0) {
			$danger_cases[$cor_str] = get_value($piece['name']);
			continue;
		}

        if (sizeof($piece['defenders']) < sizeof($piece['attackers'])) {
            $danger_cases[$cor_str] = get_value($piece['name']);   
            $score[$team_turn] += $attacked_by_piece;
            continue;
        }
 
		foreach ($piece['attackers'] as $cor) {
			$attacker_cor_str = cor_string($cor);
			$attacker_value = get_value(get_piece($board, $attacker_cor_str)['name']);
	
			if ($attacker_value < $piece['value']) {
				$danger_cases[$cor_str] = get_value($piece['name']);   
				break;
			}
		}
	}

    if ($kings_under_attack[$team_turn]) {
        $score[$team_turn] -= array_shift($danger_cases);
    } else {
        if (sizeof($danger_cases) > 1) {
            $score[$team_turn] += ((sizeof($danger_cases) - 1) * $attacked_by_piece);
        }
    }

	$opposite_score_team = opposite_team($get_score_for);
	return $full_array ? $score : $score[$get_score_for] - $score[$opposite_score_team];
}

function calculate_move(array $board, string $team_turn, int $difficulty = 1000)
{
	/**
	 * The starting place for calculating a movement based upon the $difficulty.
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param int effects the quality of the move calculation (between 0 and 1000)
	 * 
	 * @return array movement array of the recomended move
	*/
	$limit          = 0;
	$choice         = mt_rand(0, 1000);
    $opposite_team  = opposite_team($team_turn);
    $message        = '';

	if ($choice <= $difficulty) {
		$movement = recursive_calculation($board, $team_turn, 0, $limit);
        $message  = 'best_movement';
	} else {

		$choice = mt_rand(0, 1000);
		if ($choice <= $difficulty) {

			$movement = best_movements($board, $team_turn, 1)[0];
            $board    = $movement['board'];
			$movement = ['move' => $movement['move'], 'status' => ''];
            $message  = 'second_best_movement';

		} else {

            $message         = 'random_movement';
			$total_movements = [];
			foreach ($board['squares'] as $cor_str => $piece) {
				if ($piece['team'] == $team_turn && sizeof($piece['movements']) > 0) {
					$total_movements[$cor_str] = $piece['movements'];
				}
			}
	
			$from	= array_rand($total_movements);
			$to 	= array_rand($total_movements[$from]);

			$to 	= create_cor($total_movements[$from][$to]);;
            $from   = create_cor($from);

            move_piece($board, $from, $to);

			$movement = [
				'move' => [
					'from'	=> $from,
					'to'	=> $to,
				],
				'status' => '',
			];
		}

        $movement['status'] = is_square_attacked($board, $opposite_team, $board[$opposite_team.'_king']) ? 'checkmate' : 'stalemate';
        foreach ($board['squares'] as $cor_str => $piece) {
            if ($piece['team'] == $opposite_team && sizeof($piece['movements']) > 0) {
                $movement['status'] = 'active';
                break;
            }
        }
	}

    $movement['message'] = $message;
	return $movement;
}

function recursive_calculation(array $board, string $team_turn, int $current, int $limit)
{
	/**
	 * Calculate the best movement for a certain situation using the score from the board_score function
	 * Could be used to calculate result score of movements recursively 
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param int the current counter of recursiveness
	 * @param int the limit the function can recursive calculate moves
	 * 
	 * @return array array with both the advised move and the score of that move
	*/
	$opposite_team 		= opposite_team($team_turn);
	$best_moves_length	= 5;
	$best_moves 		= best_movements($board, $team_turn, $best_moves_length);
	$return_move		= null;
	

	// 
	// calulate enemy reaction to the moves
	//
	foreach ($best_moves as $move_data) {

		$enemy_move	= best_movements($move_data['board'], $opposite_team, 2);

		if ($enemy_move == 'checkmate') {
			$return_move = ['score' => 999999, 'move' => $move_data['move'], 'status' => 'checkmate'];
			break;
		}
		
		$enemy_move = $enemy_move[0];

        // dump('response move: '.from_to($enemy_move['move']).': '.$enemy_move['score']);

		$new_board 	= $enemy_move['board'];
		$move 		= $enemy_move['move'];

		move_piece($new_board, $move['from'], $move['to']);
		$score		= board_score($board, $team_turn, $team_turn);

		/**
		 * code for calculating the end result score of the movements recursively, currently not in use.
		 * 
		 *	if ($current < $limit) {
		 *		$final_move = recursive_calculation($new_board, $team_turn, $current + 1, $limit);
		 *	} else {
		 *		$final_move = best_movements($new_board, $team_turn, 1)[0];
		 *	}
		 */

        
		// if ($current < $limit) {
		//     $final_move = recursive_calculation($new_board, $team_turn, $current + 1, $limit);
		// } else {
		//     $final_move = best_movements($new_board, $team_turn, 1)[0];
		// }
		 

		if ($return_move == null || $return_move['score'] < $score) {
			$return_move = ['score' => $score, 'move' => $move_data['move'], 'status' => 'active'];
		}
	}

	return $return_move;
}

function worst_movements(array $board, string $team)
{
	/**
	 * Calculate the WORST movements for the given team. 
	 * Assuming the given team is the one that can make a move.
	 * Is used to filter out the worst movements in advance to save processing time.
	 * 
	 * @param array the board array
	 * @param string the team to calculate for
	 * 
	 * @return array array with with the worst movements 
	*/
	$pieces_array 		= get_attackers_defenders($board);
	$worst_movements 	= [];

	foreach ($pieces_array[$team] as $cor_str => $piece) {

		foreach ($piece['movements'] as $index => $cor) {
			$movement_cor_str = cor_string($cor);
			$square_name = $board['squares'][$movement_cor_str]['name'];
			$attackers = [];
			$defenders = [];

            // get all the attackers and defenders for the new location
			foreach ($pieces_array as $piece_team => $team_array) {
				foreach ($team_array as $team_cor => $team_piece) {

					if (in_array($cor, $team_piece['attack_squares']) && $cor_str != $team_cor) {
						if ($team == $piece_team) {
							$defenders[$team_cor] = $team_piece['value'];
						} else {
							$attackers[$team_cor] = $team_piece['value'];
						}
					}

				}
			}

			if (sizeof($attackers) == 0) {
				continue;
			}

			if ($square_name == '') {

				$attacked_by_lower = false;
				foreach ($attackers as $value) {
					if ($value < $piece['value']) {
						$attacked_by_lower = true;
						break;
					}
				}

				// if all attackers are higher value and piece is defended
				if (!$attacked_by_lower && sizeof($defenders) > 0) {
					continue;
				}

			} else if (get_value($square_name) >= $piece['value'] && $piece['name'] != 'king') {
				continue;
			} else if (sizeof($defenders) >= sizeof($attackers) && $piece['name'] != 'king') {
				continue;
			}

			$worst_movements[$cor_str][$index] = $cor;
		}

	}
	return $worst_movements;
}

function best_movements(array $board, string $team, int $return_array_length)
{
	/**
	 * Calculate the best movements for a team.
	 * Returns an array with the movements sorted by their score
	 * 
	 * @param array the board array
	 * @param string the team to calculate for
	 * 
	 * @param int the length of the return array. 
	 * Every item in the return array contains a board array. Keep this number low to save memory.
	 * 
	 * @return array the array with the moves sorted by score.
	*/
	$calculated_moves	= [];
	$total_movements	= [];
	$return_array       = [];
	$opposite_team		= opposite_team($team);

	if (is_square_attacked($board, $team, $board[$team.'_king'])) {
		$worst_movements = [];
		$in_check        = true;
	} else {
		$worst_movements = worst_movements($board, $team);
		$in_check        = false;
	}


	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '' || $piece['team'] != $team || sizeof($piece['movements']) == 0) {
			continue;
		}

        $total_movements[$cor_str] = $piece['movements'];
	}


    //
    // if no movements are possible
    //
    if (sizeof($total_movements) == 0) {
		return $in_check ? 'checkmate' : 'stalemate';
    }

    // 
	// filter the worst movements 
	// 
    if ($total_movements != $worst_movements) {
        foreach ($total_movements as $cor_str => $movements_arr) {

            if (isset($worst_movements[$cor_str])) {
                foreach ($worst_movements[$cor_str] as $index => $cor) {
                    unset($total_movements[$cor_str][$index]);
                }
            }
    
        }
    }

	// 
	// order remaining movements by score
	//
	foreach ($total_movements as $cor_str => $movements) {
		foreach ($movements as $cor) {

			$board_copy = $board;
			move_piece($board_copy, $cor_str, $cor);
			$score = board_score($board_copy, $opposite_team, $team);

			$calculated_moves[$score][] = [
				'score' => $score,
				'board' => $board_copy,
				'move'  => ['from' => create_cor($cor_str), 'to' => $cor],
			];
		}
	}


	//
	// return the best of the array in requested length
	// 
	krsort($calculated_moves);
	foreach ($calculated_moves as $score => $array) {
		shuffle($array);
		foreach ($array as $move_data) {
			$return_array[] = $move_data;

			$return_array_length--;
			if ($return_array_length === 0) {
				break;
			}
		}

        if ($return_array_length === 0) {
            break;
        }
	}

	return $return_array;
}


function pawn_movements(array $board, array $cor)
{
	/** 
	 * Calculate movements and the attack squares for the pawn.
     * Rather ugly code but it works..
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/
	$current_cor = $cor;
	$piece = get_piece($board, $cor);
	$team = $piece['team'];
	$opposite_team = opposite_team($team);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];

	// 1 step ahead
	$current_cor['y'] += $team == "top" ? 1 : -1;
	if (valid_cor($current_cor)) {

        $square_data = get_piece($board, $current_cor);
        if ($square_data['name'] == '') {

            $possible_movements['movements'][] = $current_cor;


            if (!$piece['has_moved']) {

                $current_cor['y'] += $team == "top" ? 1 : -1;
                $square_data = get_piece($board, $current_cor);
                if (valid_cor($current_cor) && $square_data['name'] == "") {
                    $possible_movements['movements'][] = $current_cor;
                }
                $current_cor['y'] += $team == "top" ? -1 : 1;
            }
        }
	}

	// left/right
	$current_cor['x'] += 1;
	if (valid_cor($current_cor)) {
		$square_data = get_piece($board, $current_cor);
		$possible_movements['attack_squares'][] = $current_cor;
		if ($square_data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
		}
	}

	// left/right
	$current_cor['x'] -= 2;
	if (valid_cor($current_cor)) {
		$square_data = get_piece($board, $current_cor);
		$possible_movements['attack_squares'][] = $current_cor;
		if ($square_data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
		}
	}

	return $possible_movements;
}

function horse_movements(array $board, array $cor)
{
	/** 
	 * Calculate movements and the attack squares for the horse
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/
	$team = get_piece($board, $cor)['team'];
	$opposite_team = opposite_team($team);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 2,  1],
		[ 1,  2],
		[-2,  1],
		[ 1, -2],
		[-1, -2],
		[-2, -1],
		[-1,  2],
		[ 2, -1],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		$current_cor['x'] += $direction[0];
		$current_cor['y'] += $direction[1];

		if (!valid_cor($current_cor)) {
			continue;
		}

		$piece = get_piece($board, $current_cor);

		if ($piece['name'] == "" || $piece['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
    		$possible_movements['attack_squares'][] = $current_cor;
		}
	}
	
	return $possible_movements;
}

function bishop_movements(array $board, array $cor)
{
	/** 
	 * Calculate movements and the attack squares for the bishop.
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/

	$piece = get_piece($board, $cor);
	$opposite_team = opposite_team($piece['team']);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 1,  1],
		[-1, -1],
		[ 1, -1],
		[-1,  1],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		for ($i = 0; $i < 7; $i++) {
			$current_cor['y'] += $direction[0];
			$current_cor['x'] += $direction[1];

			if (!valid_cor($current_cor)) {
				break;
			}

			$possible_movements['attack_squares'][] = $current_cor;
			$square_data = get_piece($board, $current_cor);
			if ($square_data['name'] == "" || $square_data['team'] == $opposite_team) {
				$possible_movements['movements'][] = $current_cor;
				if ($square_data['team'] == $opposite_team) {
					break;
				}
			} else {
				break;
			}

		}
	}

	return $possible_movements;
}

function tower_movements(array $board, array $cor)
{
	/** 
	 * Calculate movements and the attack squares for the tower
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/
	$piece = get_piece($board, $cor);
	$opposite_team = opposite_team($piece['team']);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 0,  1],
		[ 1,  0],
		[ 0, -1],
		[-1,  0],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		for ($i = 0; $i < 7; $i++) {

			$current_cor['y'] += $direction[0];
			$current_cor['x'] += $direction[1];

			if (!valid_cor($current_cor)) {
				break;
			}

			$possible_movements['attack_squares'][] = $current_cor;
			$square_data = get_piece($board, $current_cor);
			if ($square_data['name'] == "" || $square_data['team'] == $opposite_team) {
				$possible_movements['movements'][] = $current_cor;
				if ($square_data['team'] == $opposite_team) {
					break;
				}
			} else {
				break;
			}

		}
	}
	
	return $possible_movements;
}

function queen_movements(array $board, array $cor)
{
	/** 
	 * Calculate movements and the attack squares for the queen
	 * uses the tower and bishop calculations.
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/
	$bishop_movements = bishop_movements($board, $cor);
	$tower_movements  = tower_movements ($board, $cor);
	$total_movements = [];
	
	foreach ($bishop_movements as $key => $value) {
		$total_movements[$key] = array_merge($tower_movements[$key], $bishop_movements[$key]);
	}

	return $total_movements;
}

function king_movements(array $board, array $cor){
	/** 
	 * Calculate movements and the attack squares for the king
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares
	*/
	$towers_x               = [0,7];
	$piece                  = get_piece($board, $cor);
	$opposite_team          = opposite_team($piece['team']);
	$possible_movements     = [
		'movements'      => [],
		'attack_squares' => [],
	];
	$directions             = [
		[ 1,  1],
		[-1, -1],
		[ 0,  1],
		[ 1, -1],
		[ 1,  0],
		[-1,  1],
		[ 0, -1],
		[-1,  0],
	];
	
	foreach ($directions as $direction) {
		$new_cor = $cor;
		$new_cor['x'] += $direction[0];
		$new_cor['y'] += $direction[1];

		if (!valid_cor($new_cor)) {
			continue;
		}

		$data = get_piece($board, $new_cor);
		$possible_movements['attack_squares'][] = $new_cor;
		if ($data['name'] == "" || $data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $new_cor;
		}
	}
	
	if ($piece['has_moved'] || is_square_attacked($board, $piece['team'], $cor)) {
		return $possible_movements;
	}

	// castling movements
	foreach ($towers_x as $tower_x) {
		$tower_cor = $cor;
		$tower_cor['x'] = $tower_x;
		$tower = get_piece($board, $tower_cor);

		if ($tower['name'] == 'tower' && !$tower['has_moved'] && valid_castling($board, $cor, $tower_cor)) {
			$new_cor = $cor;
			$new_cor['x'] += $tower_x == 0 ? -2 : 2;

			$possible_movements['movements'][] = $new_cor;
		}
	}

	return $possible_movements;
}

function get_movements(array $board, array $cor)
{
	/**
	 * Get the movements for a piece based on their coordinates
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with the movements & attack squares for that piece
	*/

	$piece = get_piece($board, $cor);
	switch ($piece['name']) {
		case 'king':
			return king_movements($board, $cor);
			break;

		case 'queen':
			return queen_movements($board, $cor);
			break;

		case 'tower':
			return tower_movements($board, $cor);
			break;

		case 'bishop':
			return bishop_movements($board, $cor);
			break;

		case 'horse':
			return horse_movements($board, $cor);
			break;

		case 'pawn':
			return pawn_movements($board, $cor);
			break;
	}

	return [
		'movements' => [],
		'attack_squares' => [],
	];


}

function get_value($name)
{
	/**
	 * Get the value for a piece.
	 * 
	 * @param string the name of the piece
	 * 
	 * @return array array with all the movements & attack squares 
	*/
	switch ($name) {
		case 'king':
			return 0;
			break;
		case 'queen':
			return 950;
			break;
		case 'tower':
			return 500;
			break;
		case 'bishop':
			return 300;
			break;
		case 'horse':
			return 300;
			break;
		case 'pawn':
			return 100;
			break;
		default:
			return 0;
			break;
	}
}

function valid_castling(array $board, array $king_cor, array $tower_cor) {
	/**
	 * Check if castling movement is possible.
     * Used for the king movements calculations.
	 * 
	 * @param array the board array
	 * @param array the location of the king
	 * @param array the location of the tower you want to castle with
	 * 
	 * @return bool
	*/
	$y = $king_cor['y'];
	$king = get_piece($board, $king_cor);

	$end = $king_cor['x'] > $tower_cor['x'] ? $king_cor['x'] : $tower_cor['x'];
	$start = $king_cor['x'] > $tower_cor['x'] ? $tower_cor['x'] : $king_cor['x'];

	for ($x = $start + 1; $x < $end; $x++) {
		$square_cor = "$x-$y";
		$square_data = get_piece($board, $square_cor);
		if ($square_data['name'] != "" || is_square_attacked($board, $king['team'], create_cor($square_cor))) {
			return false;
		}
	}
	return true;
}

?>