<?php
defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="wrap" id="dts-debugger-settings-wrap">
	<h2>Scrape and Debug Settings</h2>

	<form method="post" action="options.php">

		<?php

		settings_fields('dts_settings');

		do_settings_sections('dts_settings');

		submit_button();

		?>

	</form>
</div>