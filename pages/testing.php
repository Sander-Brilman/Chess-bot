<?php

$board = $_SESSION['game_data']['board'];

$board = create_board();

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
<table id="board" style="width: 500px; height: 500px">
	<?php
	for ($i=0; $i < 8; $i++) {
		echo '<tr>';
			for ($j=0; $j < 8; $j++) {
				$cor = "$j-$i";
				$piece = get_piece($board, $cor);
				$content = $piece['name'] != '' ?'<div style="--piece-color: '.$_SESSION['game_data']['color'].'; --shadow-color: '.$_SESSION['game_data']['shadow'].';" class='.$piece['team'].'>'.$icons[$piece['name']].'</div>' : '';
				echo "<td title='$cor' id='$cor'>$content</td>";
			}
		echo '</tr>';
	}
	?>
</table>
<link rel="stylesheet" href="assets/css/play.css">
</div>

<?php

move_piece($board, '0-6', '0-5');

// -- first layer -- //

 $result = calculate_move($board, 'top');
echo BR;
echo BR;
echo BR.'result';
dump($result);


dump($board['squares']['5-3']['movements']);



$moves = best_movements($board, 'top', 5);

// echo BR;
// echo BR;
// echo BR.'result';

// dump($moves);

// foreach ($moves as $move) {

//     dump(from_to($move['move']) . ': ' . $move['score'] );

//     // foreach ($move as $key => $value) {
//     //     dump($key);
//     // }
//     echo BR;
//     echo BR;
// }




?>