<?php
require_once('functions.php');

session_start();
$board = $_SESSION['game_data']['board'];
$move = calculate_move($board, 'bottom', 1000);
echo json_encode($move['move']);
?>