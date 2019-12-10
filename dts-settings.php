<?php
    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<div class="wrap">
<h2>DT's Debugger Settings</h2>

<form method="post" action="options.php"> 

<?php
    submit_button();

	settings_fields( 'dts_settings' );

    do_settings_sections( 'dts_settings' );

    submit_button();
?>

</form>
</div>