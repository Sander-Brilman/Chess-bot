<?php
session_start();
require_once('functions.php');
$final_pieces = [];

foreach ($_SESSION['game_data']['board']['squares'] as $cor_str => $piece) {
    if ($piece['name'] == '' || $piece['team'] == 'top') {
        continue;
    }
    $movements = [];
    foreach ($piece['movements'] as $movement) {
        $movements[] = cor_string($movement);
    }
    $final_pieces[$cor_str] = $movements;
}

echo json_encode($final_pieces);
?>