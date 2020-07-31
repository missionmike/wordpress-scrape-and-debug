<?php

/**
 * Plugin setup and hooks
 */

defined('ABSPATH') or die('No script kiddies please!');



/**
 * Action links on plugin page:
 * Add 'Settings' Link
 */
function dts_dbggr_action_links($actions, $plugin_file)
{
	static $plugin;

	if (!isset($plugin)) {
		$plugin = plugin_basename(__FILE__);
	}

	if ($plugin === $plugin_file) :

		$settings = array(
			'settings' => '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=dts-debugger')) . '">' . __('Settings', 'General') . '</a>'
		);
		$actions = array_merge($settings, $actions);

	endif;

	return $actions;
}
add_filter('plugin_action_links', 'dts_dbggr_action_links', 10, 5);



/**
 * Add options on plugin activation
 */
function dts_dbggr_activate()
{
	add_option('dts_settings');
}
register_activation_hook(__FILE__, 'dts_dbggr_activate');



/**
 * Remove plugin-specific options on plugin removal
 */
function dts_dbggr_remove()
{
	delete_option('dts_settings');
}
register_deactivation_hook(__FILE__, 'dts_dbggr_remove');



/**
 * Add Scrape and Debug to Settings Menu
 */
function dts_dbggr_init_menu()
{
	function dts_dbggr_options_page()
	{
		include(plugin_dir_path(__FILE__) . '../dts-settings.php');
	}
	add_options_page(__('Scrape and Debug', 'dts-debugger'), __('Scrape and Debug', 'dts-debugger'), 'manage_options', 'dts-debugger', 'dts_dbggr_options_page');
}
add_action('admin_menu', 'dts_dbggr_init_menu');



/**
 * Add quicklinks column to posts and pages lists
 */
function dts_dbggr_post_modify_columns($columns)
{
	$new_columns = array(
		'dts_quicklinks' => __('Scrape and Debug', 'dts-debugger')
	);

	return array_merge($columns, $new_columns);
}



/**
 * Populate quicklinks column
 */
function dts_dbggr_custom_column_content($column)
{
	$options = get_option('dts_settings');

	switch ($column):

		case 'dts_quicklinks':

			$debuggers = dts_dbggr_get_data();

			foreach ($debuggers as $debugger) :

				if (!is_string($debugger)) :

					$setting_option = 'dts_debugger_' . $debugger['name'];

					if (!empty($options) && (!isset($options[$setting_option]) || $options[$setting_option] !== '1')) :

						continue;

					endif;

					echo '<a href="' . $debugger['url'] . '" target="_blank" class="debug-btn column" title="' . __('Click to check with ', 'dts-debugger') . __($debugger['title'], 'dts-debugger') . '">';
					echo '<img src="' . plugins_url('../images/' . $debugger['image'], __FILE__) . '" alt="' . $debugger['title'] . '">';
					echo '</a>';

				endif;

			endforeach;

			break;

	endswitch;
}



/** 
 * Scan for custom post types and init columns on init 
 */
function dts_dbggr_init_custom_columns()
{
	$post_types = get_post_types('', 'objects');

	foreach ($post_types as $post_type) :

		$options = get_option('dts_settings');
		$setting_option = 'dts_post_types_' . $post_type->name;

		if (empty($options[$setting_option]) || $options[$setting_option] !== '1') {
			continue;
		}

		add_filter('manage_' . $post_type->name . '_posts_columns', 'dts_dbggr_post_modify_columns');
		add_action('manage_' . $post_type->name . '_posts_custom_column', 'dts_dbggr_custom_column_content');

	endforeach;
}
add_action('admin_init', 'dts_dbggr_init_custom_columns');



/**
 * Show notice if settings are not found
 */
function dts_dbggr_activate_notice()
{
	if (!get_option('dts_settings')) :

		if (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] !== 'dts-debugger')) :

			$class = 'notice notice-warning';
			$settings_url = admin_url('options-general.php?page=dts-debugger');
			$link = '<a href="' . $settings_url . '">settings</a>';

			$message = __('Thank you for activating <strong>Scrape and Debug</strong>! Please visit the ' . $link . ' page to get started.', 'dts-debugger');

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);

		endif;

	endif;
}
add_action('admin_notices', 'dts_dbggr_activate_notice');
