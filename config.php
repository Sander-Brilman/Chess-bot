<?php
$debug_ips = [
	'::1',
];

$site_folder            = '/websites/Chess-bot/'; // Don't forget the '/' at the start & end 

$theme_color            = '#00b3cd'; // css color notation
$locate                 = 'en_US'; // language_TERRITORY format ('nl_NL' or 'en_US' for example)

$display_name                   = 'Sander\'s Chess'; // company / organization name
$default_search_title           = 'My self-made chess bot - Play now'; // about 50 characters
$default_website_description    = 'Play against my self made chess bot.'; // about 160 characters


$site_domain = $_SERVER['SERVER_NAME'];

$site_url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$site_url .= $site_domain . $site_folder;

$url_array = $_SERVER['REQUEST_URI'];
$url_array = str_replace($site_folder, '', $url_array);
$url_array = explode('/', $url_array);

foreach ($url_array as &$value) {
	$value = explode('?', $value)[0];
	$value = explode('#', $value)[0];
}

$icons = [
	'tower' 	=> '<i class="fa-solid fa-chess-rook-piece"></i>',
	'horse'	 	=> '<i class="fa-solid fa-chess-knight"></i>',
	'bishop' 	=> '<i class="fa-solid fa-chess-bishop"></i>',
	'queen' 	=> '<i class="fa-solid fa-chess-queen"></i>',
	'king' 		=> '<i class="fa-solid fa-chess-king"></i>',
	'pawn' 		=> '<i class="fa-solid fa-chess-pawn"></i>',
];
?>