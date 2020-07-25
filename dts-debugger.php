<?php
/*
Plugin Name: Scrape and Debug
Plugin URI: https://www.missionmike.dev/scrape-and-debug-wordpress-plugin/
Description: Simplify page debugging via Facebook Sharing Debugger, LinkedIn Post Inspector, Google's Structured Data Testing Tool and Rich Results Test, PageSpeed Insights, W3C Validation, and Google AMP Test.
Version: 0.5.1
Author: Michael R. Dinerstein (Mission Mike)
Author URI: https://www.missionmike.dev/
License: GPL2
*/

defined('ABSPATH') or die('No script kiddies please!');



/**
 * Register styles/scripts
 */
function dts_dbggr_register_scripts()
{

	$version = '20200715';

	wp_register_style('dts-style', plugins_url('css/styles.css', __FILE__), false, $version);
	wp_register_script('dts-scripts', plugins_url('js/dts-scripts.js', __FILE__), false, $version);
}
add_action('admin_init', 'dts_dbggr_register_scripts');



/**
 * Enqueue styles/scripts
 */
function dts_dbggr_enqueue_scripts()
{

	wp_enqueue_style('dts-style');
	wp_enqueue_script('jquery');
	wp_enqueue_script('dts-scripts');
}
add_action('admin_enqueue_scripts', 'dts_dbggr_enqueue_scripts');



/**
 * Action links on plugin page:
 * Add 'Settings' Link
 */
function dts_dbggr_action_links($actions, $plugin_file)
{

	static $plugin;

	if (!isset($plugin)) :

		$plugin = plugin_basename(__FILE__);

	endif;

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
 * Remove plugin-specific options on plugin deactivation
 */
function dts_dbggr_remove()
{

	delete_option('dts_settings');
}
register_deactivation_hook(__FILE__, 'dts_dbggr_remove');



/**
 * Init plugin on admin_init
 */
function dts_dbggr_init()
{

	load_plugin_textdomain('dts-debugger', false, basename(dirname(__FILE__)) . '/languages');
	register_setting('dts_settings', 'dts_settings', 'dts_dbggr_settings_validate');


	function dts_dbggr_settings_show_option()
	{
		echo '<p>If you wish to use a particular service, make sure it is checked here.</p>';
	}

	add_settings_section('dts_settings_debuggers', __('Available Debuggers/Tools', 'dts-debugger'), 'dts_dbggr_settings_show_option', 'dts_settings');

	$debuggers = dts_dbggr_get_data();
	$debugger_category = '';

	foreach ($debuggers as $debugger) :

		if (is_string($debugger)) :

			$debugger_category = $debugger;
			continue;

		endif;


		$dts_settings_show_option = function () use ($debugger, $debugger_category) {

			$options = get_option('dts_settings');
			$setting_name = 'dts_debugger_' . $debugger['name'];

			if (empty($options)) :

				$options = array();
				$options[$setting_name] = '1';

			endif;

			if (empty($options)) :

				$options = array();

			endif;

			if (!isset($options[$setting_name])) :

				$options[$setting_name] = 'unchecked';

			endif;

			$dts_class = $options[$setting_name] === '1' ? 'checked' : 'unchecked';

?>
			<div class="dts_settings_debuggers <?php echo $dts_class; ?>" id="dts_settings_<?php echo $setting_name; ?>" style="background-image:url(<?php echo plugins_url('images/' . $debugger['image'], __FILE__); ?>">
				<label for="dts_checkbox_<?php echo $setting_name; ?>"></label>
				<input type="checkbox" name="dts_settings[<?php echo $setting_name; ?>]" id="dts_checkbox_<?php echo $setting_name; ?>" value="1" <?php checked($options[$setting_name], '1'); ?> />
			</div>
		<?php
		};

		add_settings_field('dts_debugger_' . $debugger['name'], $debugger['title'], $dts_settings_show_option, 'dts_settings', 'dts_settings_debuggers');

	endforeach;


	function dts_dbggr_settings_post_types_text()
	{
		echo '<p>Select which post types <strong>display</strong> the <em>Scrape and Debug</em> panel and icon links:</p>';
	}

	add_settings_section('dts_settings_post_types', __('Show on Post Types:', 'dts-debugger'), 'dts_dbggr_settings_post_types_text', 'dts_settings');

	$post_types = get_post_types('', 'objects');

	foreach ($post_types as $post_type) :

		if ($post_type->name === 'attachment' || $post_type->name === 'revision' || $post_type->name === 'nav_menu_item' || $post_type->name === 'acf') :

			continue;

		endif;

		$dts_settings_post_type_field = function () use ($post_type) {

			$options = get_option('dts_settings');
			$setting_name = 'dts_post_types_' . $post_type->name;

			if (empty($options)) :

				$options = array();

			endif;

			if (!isset($options[$setting_name])) :

				if ($post_type->name === 'post' || $post_type->name === 'page') :

					$options[$setting_name] = '1';

				else :

					$options[$setting_name] = false;

				endif;

			endif;

			$options[$setting_name] = isset($options[$setting_name]) ? $options[$setting_name] : false;
		?>
			<input type="checkbox" name="dts_settings[<?php echo $setting_name; ?>]" value="1" <?php checked($options[$setting_name], 1); ?> />
<?php
		};

		$label = $post_type->labels->name;

		if ($post_type->name === 'post' || $post_type->name === 'page') :

			$label .= '*';

		endif;

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



/**
 * Add Scrape and Debug to Settings Menu
 */
function dts_dbggr_init_menu()
{

	function dts_dbggr_options_page()
	{
		include(plugin_dir_path(__FILE__) . 'dts-settings.php');
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

	$filtered_columns = array_merge($columns, $new_columns);

	return $filtered_columns;
}



/**
 * Populate quicklinks column
 */
function dts_dbggr_custom_column_content($column)
{

	global $post;

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
					echo '<img src="' . plugins_url('images/' . $debugger['image'], __FILE__) . '" alt="' . $debugger['title'] . '">';
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

		if (empty($options[$setting_option]) || $options[$setting_option] !== '1') :

			continue;

		endif;

		add_filter('manage_' . $post_type->name . '_posts_columns', 'dts_dbggr_post_modify_columns');
		add_action('manage_' . $post_type->name . '_posts_custom_column', 'dts_dbggr_custom_column_content');

	endforeach;
}
add_action('admin_init', 'dts_dbggr_init_custom_columns');



/**
 * Add metabox to post/page editor
 */
function dts_dbggr_adding_metabox($post_type, $post)
{

	$options = get_option('dts_settings');
	$setting_option = 'dts_post_types_' . $post_type;

	if (!empty($options[$setting_option]) && $options[$setting_option] === '1') :

		add_meta_box('sm-debug-post', __('Scrape and Debug', 'dts-debugger'),  'dts_dbggr_social_media_metabox', null, 'side', 'core');

	endif;
}

function dts_dbggr_social_media_metabox($post)
{

	$options = get_option('dts_settings');
	$setting_option = 'dts_post_types_' . $post->post_type;

	if (empty($options[$setting_option]) || $options[$setting_option] !== '1') :

		die();

	endif;

	$debuggers = dts_dbggr_get_data();

	echo '<div class="debug-wrapper">';

	foreach ($debuggers as $debugger) :

		if (is_string($debugger)) :

			echo '<h3 class="debug-btn-title">' . $debugger . ':</h3>';

		else :

			$setting_option = 'dts_debugger_' . $debugger['name'];

			if (!empty($options) && (!isset($options[$setting_option]) || $options[$setting_option] !== '1')) :

				continue;

			endif;

			echo '<div class="debug-btn">';
			echo '<a href="' . $debugger['url'] . '" target="_blank" class="debug-btn" title="' . __('Click to check with: ', 'dts-debugger') . __($debugger['title'], 'dts-debugger') . '">';
			echo '<img src="' . plugins_url('images/' . $debugger['image'], __FILE__) . '" alt="' . $debugger['title'] . '">';

			_e($debugger['title'], 'dts-debugger');

			echo '</a>';
			echo '</div>';

		endif;

	endforeach;

	echo '</div>';
}
add_action('add_meta_boxes', 'dts_dbggr_adding_metabox', 10, 2);



/**
 * Internal data
 */
function dts_dbggr_get_data()
{

	global $post;

	if (!isset($post) || !isset($post->ID)) :

		$permalink = 'javascript:;';

	else :

		$permalink = rawurlencode(get_permalink($post->ID));

	endif;

	$debuggers = array(

		'Social Media',

		array(
			'name'	=> 'facebook',
			'url'   => 'https://developers.facebook.com/tools/debug/sharing/?q=' . $permalink,
			'title' => 'Facebook Sharing Debugger',
			'image' => 'facebook.png',
		),
		array(
			'name'	=> 'linkedin',
			'url'	=> 'https://www.linkedin.com/post-inspector/inspect/' . $permalink,
			'title' => 'LinkedIn Post Inspector',
			'image' => 'linkedin.png',
		),

		'Performance',

		array(
			'name'	=> 'pagespeed',
			'url'   => 'https://developers.google.com/speed/pagespeed/insights/?url=' . $permalink,
			'title' => 'Google PageSpeed Insights',
			'image' => 'pagespeed.png',
		),
		array(
			'name'	=> 'w3c',
			'url'   => 'https://validator.w3.org/nu/?doc=' . $permalink,
			'title' => 'Nu Html Checker (W3C)',
			'image' => 'w3c.png',
		),

		'SEO',

		array(
			'name'	=> 'google-rich',
			'url'   => 'https://search.google.com/test/rich-results?url=' . $permalink,
			'title' => 'Rich Results Test',
			'image' => 'google.png',
		),
		array(
			'name'	=> 'google',
			'url'   => 'https://search.google.com/structured-data/testing-tool/u/0/?hl=en#url=' . $permalink,
			'title' => 'Structured Data Testing Tool (Deprecated)',
			'image' => 'google-deprecated.png',
		),
		array(
			'name'  => 'amp',
			'url'   => 'https://search.google.com/test/amp?url=' . $permalink,
			'title' => 'Google AMP Test',
			'image' => 'amp.png'
		)
	);

	return $debuggers;
}
