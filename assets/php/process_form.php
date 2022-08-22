<?php
if (check_form_id('get_name')) {
    if (isset($_POST['username']) && strlen($_POST['username']) <= 15) {
        setcookie('name', $_POST['username'], time() * 4, '/');
        redirect('new-game');
    }
}

if (check_form_id('new_game')) {
	$required = [
		'color',
		'level',
		'custom_color',
		'custom_shadow',
	];

	foreach ($required as $name) {
		if (!isset($_POST[$name])) {
			redirect('new-game?error=incomplete');
		}
	} 

	if ($_POST['color'] == 'custom') {
		$color = $_POST['custom_color'];
		$shadow = $_POST['custom_shadow'];
		$begins = false;
	} else {
		$color = $_POST['color'];
		$shadow = $_POST['color'] == 'black' ? '#ff0000' : '#00c5ff';
		$begins = $color == 'white' ? true : false;
	}
    
	$computer_users = [
		['icon' => '<i class="fa-duotone fa-user-vneck-hair-long"></i>',	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-nurse-hair-long"></i>',    'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-helmet-safety"></i>', 		'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-shakespeare"></i>', 		'name' => 'Shakespeare'],
		['icon' => '<i class="fa-duotone fa-user-vneck-hair"></i>',      	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-astronaut"></i>',          'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-graduate"></i>',           'name' => 'Professor W.B'],
		['icon' => '<i class="fa-duotone fa-user-injured"></i>', 			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-doctor"></i>',            	'name' => 'Doctor *'],
		['icon' => '<i class="fa-duotone fa-user-cowboy"></i>',            	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-police"></i>', 			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-secret"></i>', 			'name' => 'Incognitor'],
		['icon' => '<i class="fa-duotone fa-user-alien"></i>',            	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-crown"></i>',          	'name' => 'King *'],
		['icon' => '<i class="fa-duotone fa-user-ninja"></i>', 				'name' => 'Ninja *'],
		['icon' => '<i class="fa-duotone fa-user-pilot"></i>', 				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-robot"></i>', 				'name' => 'Wall-E'],
		['icon' => '<i class="fa-duotone fa-user-visor"></i>',              'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-vneck"></i>',    			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-chef"></i>',              	'name' => 'Chef *'],
		['icon' => '<i class="fa-duotone fa-user-tie"></i>', 				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-angel"></i>',					'name' => '*'],
		['icon' => '<i class="fa-solid fa-user"></i>',                     	'name' => '*'],

		['icon' => '<i class="fa-duotone fa-alien-8bit"></i>', 				'name' => 'Space-Invader'],
		['icon' => '<i class="fa-duotone fa-axe-battle"></i>',				'name' => 'Viking *'],
		['icon' => '<i class="fa-duotone fa-mushroom"></i>',				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-baguette"></i>', 				'name' => 'Baguette'],
		['icon' => '<i class="fa-duotone fa-skull"></i>', 					'name' => '*'],
		['icon' => '<i class="fa-duotone fa-otter"></i>', 					'name' => '* the Otter'],
		['icon' => '<i class="fa-duotone fa-cat"></i>', 					'name' => 'Minoes'],
	];

	// random bot with random name
	$names				= explode("\n", file_get_contents('assets/names.txt'));
	$random_name		= $names[array_rand($names, 1)];
	$random_bot			= $computer_users[array_rand($computer_users, 1)];
	$random_bot['name'] = str_replace('*',  $random_name, $random_bot['name']);
	$random_bot['name'] = str_replace("\r", '', $random_bot['name']);

	$_SESSION['game_data'] = [
		'level'     	=> (int)$_POST['level'],
		'bot_icon'  	=> $random_bot['icon'],
		'bot_name'  	=> $random_bot['name'],

		'color'     	=> $color,
		'shadow'    	=> $shadow,
		'begins'    	=> $begins,
		'board'     	=> create_board(),

		'status'    	=> 'active',
		'winner'    	=> 'undefined',
		'move_history'	=> [],
	];

	redirect('play');
}

?>