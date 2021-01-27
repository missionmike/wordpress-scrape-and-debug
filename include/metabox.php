<?php

/**
 * The main plugin; init and display features
 */

defined('ABSPATH') or die('No script kiddies please!');



/**
 * Add metabox to post/page editor
 */
function dts_dbggr_adding_metabox($post_type, $post)
{

	$options = get_option('dts_settings');
	$setting_option = 'dts_post_types_' . $post_type;

	if (!empty($options[$setting_option]) && $options[$setting_option] === '1') {
		add_meta_box('sm-debug-post', __('Scrape and Debug', 'dts-debugger'),  'dts_dbggr_social_media_metabox', null, 'side', 'core');
	}
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

			echo '<br style="clear:both;">';
			echo '<h3 class="debug-btn-title">' . $debugger . '</h3>';

		else :

			$setting_option = 'dts_debugger_' . $debugger['name'];

			if (!empty($options) && (!isset($options[$setting_option]) || $options[$setting_option] !== '1')) :

				continue;

			endif;

			echo '<div class="debug-btn">';
			echo '<a href="' . $debugger['url'] . '" target="_blank" class="debug-btn" title="' . __('Click to check with: ', 'dts-debugger') . __($debugger['title'], 'dts-debugger') . '">';
			echo '<img src="' . plugins_url('../images/' . $debugger['image'], __FILE__) . '" alt="' . $debugger['title'] . '">';

			_e($debugger['title'], 'dts-debugger');

			echo '</a>';
			echo '</div>';

		endif;

	endforeach;
	
	echo '<br style="clear:both;">';

	echo '</div>';
}
add_action('add_meta_boxes', 'dts_dbggr_adding_metabox', 10, 2);
