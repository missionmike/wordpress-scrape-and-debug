<?php

/**
 * Plugin settings page
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Init plugin on admin_init
 */
function dts_dbggr_init()
{

	load_plugin_textdomain('dts-debugger', false, basename(dirname(__FILE__)) . '/languages');
	register_setting('dts_settings', 'dts_settings', 'dts_dbggr_settings_validate');

	function dts_dbggr_settings_show_option()
	{
		echo '<p>If you wish to use a particular debugger or scraper, make sure it is checked here. <br />This will make the option visible on post/page or custom post types (if enabled), <br />as well as in the quicklinks section on post/page lists.</p>';
	}

	add_settings_section('dts_settings_debuggers', __('Available Debuggers/Tools', 'dts-debugger'), 'dts_dbggr_settings_show_option', 'dts_settings');

	$debuggers = dts_dbggr_get_data();
	$debugger_category = '';

	foreach ($debuggers as $debugger) :

		if (is_string($debugger)) {
			$debugger_category = $debugger;
			continue;
		}

		$dts_settings_show_option = function () use ($debugger, $debugger_category) {

			$options = get_option('dts_settings');
			$setting_name = 'dts_debugger_' . $debugger['name'];

			if (empty($options)) {
				$options = array();
				$options[$setting_name] = '1';
			}

			if (!isset($options[$setting_name])) {
				$options[$setting_name] = 'unchecked';
			}

			$dts_class = $options[$setting_name] === '1' ? 'checked' : 'unchecked';

?>
			<div class="dts_settings_debuggers_wrapper">
				<div class="dts_settings_debuggers <?php echo $dts_class; ?>" id="dts_settings_<?php echo $setting_name; ?>" style="background-image:url(<?php echo plugins_url('../images/' . $debugger['image'], __FILE__); ?>">
					<label for="dts_checkbox_<?php echo $setting_name; ?>"></label>
					<input type="checkbox" name="dts_settings[<?php echo $setting_name; ?>]" id="dts_checkbox_<?php echo $setting_name; ?>" value="1" <?php checked($options[$setting_name], '1'); ?> />
				</div>
				<div class="dts_settings_debuggers_info">
					<p>
						<?php echo isset($debugger['about']) ? $debugger['about'] : ''; ?>
					</p>
				</div>
				<br style="clear:both;">
			</div>
		<?php
		};

		add_settings_field('dts_debugger_' . $debugger['name'], $debugger['title'], $dts_settings_show_option, 'dts_settings', 'dts_settings_debuggers');

	endforeach;


	function dts_dbggr_settings_post_types_text()
	{
		echo '<p class="dts_debugger_select_post_types">Select which post types <strong>display</strong> the <em>Scrape and Debug</em> panel and icon links:</p>';
	}

	add_settings_section('dts_settings_post_types', __('Show on the following Post Types:', 'dts-debugger'), 'dts_dbggr_settings_post_types_text', 'dts_settings');

	$post_types = get_post_types('', 'objects');
	$post_types_skip = dts_dbggr_get_post_types_skip();

	foreach ($post_types as $post_type) :

		if (in_array($post_type->name, $post_types_skip)) {
			continue;
		}

		$dts_settings_post_type_field = function () use ($post_type) {

			$options = get_option('dts_settings');
			$setting_name = 'dts_post_types_' . $post_type->name;
			$disabled = '';

			if (empty($options)) {
				$options = array();
			}

			if ($post_type->name === 'post' || $post_type->name === 'page') {
				$options[$setting_name] = '1';
				$disabled = 'class="disabled"';
			} else {
				$options[$setting_name] = false;
			}

			$options[$setting_name] = isset($options[$setting_name]) ? $options[$setting_name] : false;
		?>
			<input type="checkbox" name="dts_settings[<?php echo $setting_name; ?>]" value="1" <?php checked($options[$setting_name], 1); ?> <?php echo $disabled; ?> />
<?php
		};

		$label = $post_type->labels->name . ' (' . $post_type->name . ')';

		if ($post_type->name === 'post' || $post_type->name === 'page') {
			$label .= '*';
		}

		add_settings_field('dts_post_types_' . $post_type->name, $label, $dts_settings_post_type_field, 'dts_settings', 'dts_settings_post_types');

	endforeach;

	function dts_dbggr_settings_post_types_disclaimer()
	{
		echo '<p>*Standard <em>post</em> and <em>page</em> type cannot be disabled.</p>';
	}

	add_settings_section('dts_settings_post_types_disclaimer', '', 'dts_dbggr_settings_post_types_disclaimer', 'dts_settings');
}
add_action('admin_init', 'dts_dbggr_init');



/**
 * Validate plugin settings on save
 */
function dts_dbggr_settings_validate($input)
{
	/* Add validations for data here. */
	return $input;
}
