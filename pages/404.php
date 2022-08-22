<?php
/**
 * This file gets automatically loaded when no pages are set for the current url.
 * 
 * You can configure this in page_builder.php
 */
?>
<style>
    body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    a {
        color: white;
    }
</style>
<h1>I have searched far and wide but could not find the page. Sorry,</h1>
<a href="<?= url('new-game') ?>">New game</a>