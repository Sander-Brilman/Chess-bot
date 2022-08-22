<?php
function incomplete_form()
{
	?>
	<div class="error">
		<h2>Error</h2>
		<p>Oeps. Looks like something went wrong wile creating the match. Please try again.</p>
		<small>Does this occur often? <a href="https://sanderbrilman.nl/pages/contact.php">contact me</a></small>
	</div>
	<?php
}

function random_color()
{
	return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}
$shadow = random_color();
$color  = random_color();

if (isset($_GET['error'])) {
	switch ($_GET['error']) {
		case 'incomplete':
			incomplete_form();
			break;
	}
}
?>
<div id="menu">
	<header>
		<h1>New game</h1>
	</header>
	<form method="post">
		<div class="input-row">
			<label for="color">Color</label>

			<div class="radio-buttons">

				<div class="radio-container">
					<input checked type="radio" name="color" value="white" required >
					<i style="--color: white; --shadow: #00c5ff 0px 0px 4px;" class="fa-solid fa-chess"></i>
					White
				</div>
				<hr>

				<div class="radio-container">
					<input type="radio" name="color" value="black" required>
					<i style="--color: black; --shadow: #ff0000 0px 0px 4px;" class="fa-solid fa-chess"></i>
					Black
				</div>
				<hr>

				<div class="radio-container">
					<input type="radio" name="color" value="custom" required>
					<i style="--color: <?= $color ?>; --shadow: <?= $shadow ?> 0px 0px 4px;" class="fa-solid fa-chess"></i>
					<span>Custom<i id="toggle" class="fa-thin fa-circle-chevron-down"></i></span>
				</div>

			</div>

			<div class="color-picker">
				<div class="color-row">
					<label for="custom_color">Color</label>
					<input type="color" name="custom_color" id="custom_color" value="<?= $color ?>">
				</div>

				<div class="color-row">
					<label for="custom_shadow">Shadow color</label>
					<input type="color" name="custom_shadow" id="custom_shadow" value="<?= $shadow ?>">
				</div>
			</div>
		</div>

		<div class="input-row">
			<label for="level">Difficulty<span> - 5</span></label>
			<input type="range" name="level" min="0" max="1000" value="1000" id="level">
		</div>

		<button name="new_game" value="<?= create_form_id('new_game') ?>" type="submit">Play</button>
	</form>
</div>