<?php

/**
 * Plugin base data
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Internal data
 */
function dts_dbggr_get_data()
{
	global $post;

	$permalink = (!isset($post) || !isset($post->ID)) ? 'javascript:;' : $permalink = rawurlencode(get_permalink($post->ID));

	$debuggers = array(

		'Social Media',

		array(
			'name'    => 'facebook',
			'url'   => 'https://developers.facebook.com/tools/debug/sharing/?q=' . $permalink,
			'title' => 'Facebook Sharing Debugger',
			'image' => 'facebook.png',
			'about' => 'Want to check how your content will appear when shared on Facebook? Enable this option. Learn more about the <a href="https://developers.facebook.com/tools/debug/" target="_blank" rel="nofollow noopener">Facebook Sharing Debugger</a>.',
		),
		array(
			'name'    => 'linkedin',
			'url'    => 'https://www.linkedin.com/post-inspector/inspect/' . $permalink,
			'title' => 'LinkedIn Post Inspector',
			'image' => 'linkedin.png',
			'about' => 'Ensure this option is enabled in order to check how your content will appear when shared on LinkedIn. Learn more about the <a href="https://www.linkedin.com/post-inspector/" target="_blank" rel="nofollow noopener">LinkedIn Post Inspector</a>.',
		),

		'Performance',

		array(
			'name'    => 'pagespeed',
			'url'   => 'https://developers.google.com/speed/pagespeed/insights/?url=' . $permalink,
			'title' => 'Google PageSpeed Insights',
			'image' => 'pagespeed.png',
			'about' => 'If you want to test various URLs for page speed, enable this option. Learn more about <a href="https://developers.google.com/speed/pagespeed/insights/" target="_blank" rel="nofollow noopener">PageSpeed Insights</a>.',
		),
		array(
			'name'    => 'w3c',
			'url'   => 'https://validator.w3.org/nu/?doc=' . $permalink,
			'title' => 'Nu Html Checker (W3C)',
			'image' => 'w3c.png',
			'about' => 'Concerned about valid HTML and DOM structure? Use this tool. Learn about <a href="https://validator.w3.org/" target="_blank" rel="nofollow noopener">W3C\'s Markup Validation Service</a>.',
		),

		'SEO',

		array(
			'name'    => 'google-rich',
			'url'   => 'https://search.google.com/test/rich-results?url=' . $permalink,
			'title' => 'Rich Results Test',
			'image' => 'google.png',
			'about' => 'Google Rich Results Test is the best way to ensure your pages can generate quality search results. <a href="https://search.google.com/test/rich-results" target="_blank" rel="nofollow noopener">Learn more</a>.',
		),
		array(
			'name'    => 'google',
			'url'   => 'https://search.google.com/structured-data/testing-tool/u/0/?hl=en#url=' . $permalink,
			'title' => 'Structured Data Testing Tool (Deprecated)',
			'image' => 'google-deprecated.png',
			'about' => 'The Google Structured Data Testing Tool predates the Rich Results Test, and is deprecated. <a href="https://search.google.com/structured-data/testing-tool" target="_blank" rel="nofollow noopener">Learn more here</a>.',
		),
		array(
			'name'  => 'amp',
			'url'   => 'https://search.google.com/test/amp?url=' . $permalink,
			'title' => 'Google AMP Test',
			'image' => 'amp.png',
			'about' => 'Want to check your URLs\' AMP compatibility? Then ensure this option is enabled. <a href="https://search.google.com/test/amp" target="_blank" rel="nofollow noopener">Learn more about the AMP Test</a>.',
		)
	);

	return $debuggers;
}



/**
 * Post types to skip
 */
function dts_dbggr_get_post_types_skip()
{
	return array(
		'attachment',
		'revision',
		'nav_menu_item',
		'acf',
		'acf-field-group',
		'acf-field',
		'wpcf7_contact_form',
		'customize_changeset',
		'oembed_cache',
		'custom_css',
		'wp_block',
		'user_request',

	);
}
