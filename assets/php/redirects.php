<?php
// remove trailing '/' from url to improve SEO
if (end($url_array) == '' && str_replace($site_folder, '', $_SERVER['REQUEST_URI']) !== '') {
    redirect('', true, true);
}

if ($url_array[0] != 'welcome' && !isset($_COOKIE['name'])) {
    redirect('welcome');
}

if (!isset($_SESSION['game_data']['board'])) {
    if ($url_array[0] != 'new-game' && $url_array[0] != 'welcome') {
        redirect('new-game');
    }
}


?>