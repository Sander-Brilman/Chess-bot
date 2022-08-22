<?php
$board = $_SESSION['game_data']['board'];

// set overlay data in case of page refreshing
if ($_SESSION['game_data']['status'] == 'active') {
    $overlay_text = 'Oeps something went terribly wrong..';
    $show_overlay = '';
} else {
    if ($_SESSION['game_data']['status'] == 'checkmate') {
        $overlay_text = $_SESSION['game_data']['winner'].' won!';
    } else {
        $overlay_text = 'Draw';
    }
    $show_overlay = 'show';
}

$pieces_on_team = [
	'top' => [
		'pawn' 		=> 8,
		'horse' 	=> 2,
		'bishop' 	=> 2,
		'tower' 	=> 2,
		'queen' 	=> 1,
		'king' 		=> 1,
	],
	'bottom' => [
		'pawn' 		=> 8,
		'horse' 	=> 2,
		'bishop' 	=> 2,
		'tower' 	=> 2,
		'queen' 	=> 1,
		'king' 		=> 1, 
	],
];
$captured = [
	'top' 		=> '',
	'bottom' 	=> '',
];

foreach ($board['squares'] as $piece) {
	if ($piece['name'] == '') {
		continue;
	}
	$pieces_on_team[$piece['team']][$piece['name']]--;
}
foreach ($pieces_on_team as $team => $missing_array) {
	foreach ($missing_array as $name => $amount_missing) {
		$captured[$team] .= str_repeat($icons[$name], $amount_missing);
	}
}

$color_2 	= $_SESSION['game_data']['color'] == 'white' ? 'rgb(135 91 44)' : 'wheat';
$color_1 	= $_SESSION['game_data']['color'] == 'white' ? 'wheat' : 'rgb(135 91 44)';
$css_vars 	= '
--piece-color: '.$_SESSION['game_data']['color'].';
--shadow-color: '.$_SESSION['game_data']['shadow'].';
--square-color-1: '.$color_1.';
--square-color-2: '.$color_2.';
';
?>
<div class="main" style="<?= $css_vars ?>">
	<header>
		<a href="http://schaken.sanderbrilman.nl/index.php?version-select=true"><i class="fa-duotone fa-chevron-left"></i> Versions</a>
		<h1>Chess</h1>
        <!-- this is here to make sure the width of the text is the same on both sides -->
		<a target="_blank" href="https://github.com/Sander-Brilman/Chess-bot-english">Github repo <i class="fa-duotone fa-chevron-right"></i></a>
	</header>
	<div class="content">
		<div class="game-container">

			<div class="player-info">
				<div class="user">
					<?= $_SESSION['game_data']['bot_icon'] ?>
					<div>
						<span><?= $_SESSION['game_data']['bot_name'] ?></span>
						<span><?= $_SESSION['game_data']['level'] ?></span>
					</div>
				</div>

				<div class="captured bottom"><?= $captured['bottom'] ?></div>
			</div>

            <div class="board-container">
                <table id="board">
                    <?php
                    for ($i=0; $i < 8; $i++) {
                        echo '<tr>';
                            for ($j=0; $j < 8; $j++) {
                                $cor = "$j-$i";
                                $piece = get_piece($board, $cor);
                                $content = $piece['name'] != '' ? generate_piece_html($piece['name'], $piece['team']) : '';
                                echo "<td id='$cor'>$content</td>";
                            }
                        echo '</tr>';
                    }
                    ?>
                </table>
                <div id="overlay" class="<?= $show_overlay ?>">
                    <h2><?= $overlay_text ?></h2>
                    <a href="<?= url('new-game') ?>">New game</a>
                </div>
            </div>


			<div class="player-info">
				<div class="user">
                    <i class="fa-duotone fa-user"></i>
                    <div>
						<span><?= $_COOKIE['name'] ?></span>
						<span>(You)</span>
					</div>
				</div>

				<div class="captured top"><?= $captured['top'] ?></div>
			</div>

		</div>

		<div class="side-panel">
			<h2>Options</h2>
			<div class="splitter"></div>
			<button id="undo">Undo</button>
			<button id="hint">Hint</button>
			<button><a href="<?= url('new-game') ?>">New game</a></button>
		</div>
	</div>
</div>
<?php

if (!$_SESSION['game_data']['begins']) {

    $move = calculate_move($board, 'top')['move'];

    $_SESSION['game_data']['board'] = $board;
    $_SESSION['game_data']['begins'] = true;

    $from   = cor_string($move['from']);
    $to     = cor_string($move['to']);

    $_SESSION['game_data']['move_history'][] = [

        'from'  => [
            'coordinate' => $from,
            'piece'      => get_piece($board, $from),
        ],
        'to'    => [
            'coordinate' => $to,
            'piece'      => get_piece($board, $to),
		],

        'castling' => check_castling($board, $move['from'], $move['to']),
    ];

    move_piece($board, $move['from'], $move['to']);
    ?>
    <script>
        setTimeout(() => {
            let toCor = JSON.parse('<?= json_encode($move['to']) ?>');
            let fromCor = JSON.parse('<?= json_encode($move['from']) ?>');;

            let toElement = document.getElementById(`${toCor.x}-${toCor.y}`);
            let fromElement = document.getElementById(`${fromCor.x}-${fromCor.y}`);

            let cellHeight = document.querySelector('td').offsetHeight;
            let cellWidth = document.querySelector('td').offsetWidth;

            let data = fromElement.querySelector('div');

            data.style.left = ((toCor.x - fromCor.x) * cellWidth) + "px";
            data.style.top = ((toCor.y - fromCor.y) * cellHeight)+ "px";

            setTimeout(() => {
                data.style.left = '0px';
                data.style.top = '0px';

                fromElement.classList.add("move-from");
                toElement.classList.add("move-to");

                toElement.innerHTML = '';
                toElement.appendChild(data);
            }, 500);
        }, 300);
    </script>
    <?php
}
?>