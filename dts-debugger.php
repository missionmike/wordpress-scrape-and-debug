<?php
   /*
   Plugin Name: DT's Debugger
   Plugin URI: https://dtweb.design/debugger/
   Description: Simplify page debugging via Facebook Developer Tools, Google's Structured Data Testing Tool, PageSpeed Insights, W3C Validation, Google AMP Test. Found in page/post sidebar metabox and edit posts/pages/CPT lists.
   Version: 0.3
   Author: Michael R. Dinerstein
   Author URI: https://dtweb.design/
   License: GPL2
   */
   
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/**
 * Register and enqueue styles/scripts
 */
function dts_dbggr_register_scripts() {
    $version = '03032017';

    wp_register_style( 'dts-style', plugins_url( 'css/styles.css', __FILE__ ), false, $version );
    wp_register_script( 'dts-scripts', plugins_url( 'js/dts-scripts.js', __FILE__ ), false, $version );
}
add_action( 'admin_init', 'dts_dbggr_register_scripts' );

function dts_dbggr_enqueue_scripts() {
    wp_enqueue_style( 'dts-style' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'dts-scripts' );
}
add_action( 'admin_enqueue_scripts', 'dts_dbggr_enqueue_scripts' );


/**
 * Action links on plugin page:
 * Add 'Settings' Link
 */
function dts_dbggr_action_links( $actions, $plugin_file ) {
    static $plugin;

    if ( !isset( $plugin ) )
        $plugin = plugin_basename( __FILE__ );
    if ( $plugin === $plugin_file ) :

        $settings = array(
            'settings' => '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=dts-debugger' ) ) . '">' . __('Settings', 'General') . '</a>'
        );
        $actions = array_merge( $settings, $actions );

    endif;

    return $actions;
}
add_filter( 'plugin_action_links', 'dts_dbggr_action_links', 10, 5 );


/**
 * Add options on plugin activation
 */
function dts_dbggr_activate() {
    add_option( 'dts_settings' );
}
register_activation_hook( __FILE__, 'dts_dbggr_activate' );


/**
 * Remove plugin-specific options on plugin deactivation
 */
function dts_dbggr_remove() {
    delete_option( 'dts_settings' );
}
register_deactivation_hook( __FILE__, 'dts_dbggr_remove' );


/**
 * Init plugin on admin_init
 */
function dts_dbggr_init() {

    load_plugin_textdomain('dts-debugger', false, basename( dirname( __FILE__ ) ) . '/languages' );
    register_setting( 'dts_settings', 'dts_settings', 'dts_dbggr_settings_validate' );

   
    function dts_dbggr_settings_show_option() {
    	echo '<p>If you wish to use a particular service, make sure it is checked here.</p>';
    }

    add_settings_section( 'dts_settings_debuggers', __( 'Available Debuggers/Tools', 'dts-debugger' ), 'dts_dbggr_settings_show_option', 'dts_settings' );
    $debuggers = dts_dbggr_get_data();
    $debugger_category = '';
    foreach( $debuggers as $debugger ) :

    	if ( is_string( $debugger ) ) :
    		$debugger_category = $debugger;
            continue;
        endif;

    	$dts_settings_show_option = function() use ( $debugger, $debugger_category ) {
    		$options = get_option( 'dts_settings' );
   			$setting_name = 'dts_debugger_' . $debugger['name'];
            
            if ( empty( $options ) ) :
                $options = array();
                $options[$setting_name] = '1';
            endif;

            if ( empty( $options ) )
            	$options = array();

            if ( ! isset( $options[$setting_name] ) )
            	$options[$setting_name] = 'unchecked';

            $dts_class = $options[$setting_name] === '1' ? 'checked' : 'unchecked';

            ?>
    		<div class="dts_settings_debuggers <?= $dts_class; ?>" id="dts_settings_<?= $setting_name; ?>" style="background-image:url(<?= plugins_url( 'images/' . $debugger['image'], __FILE__ ); ?>">
            	<label for="dts_checkbox_<?= $setting_name; ?>"></label>
    			<input type="checkbox" name="dts_settings[<?= $setting_name; ?>]" id="dts_checkbox_<?= $setting_name; ?>" value="1" <?php checked( $options[$setting_name], '1' ); ?> />
    		</div>
    		<?php
    	};

    	add_settings_field( 'dts_debugger_' . $debugger['name'], $debugger['title'], $dts_settings_show_option, 'dts_settings', 'dts_settings_debuggers' );
    endforeach;


    function dts_dbggr_settings_post_types_text() {
        echo '<p>Select which post types <strong>display</strong> the <em>DT\'s Debugger</em> panel and quicklinks:</p>';
    }

    add_settings_section( 'dts_settings_post_types', __( 'Show on Post Types:', 'dts-debugger' ), 'dts_dbggr_settings_post_types_text', 'dts_settings' );

    $post_types = get_post_types( '', 'objects' );
    foreach ( $post_types as $post_type ) :
        if ( $post_type->name === 'attachment' || $post_type->name === 'revision' || $post_type->name === 'nav_menu_item' || $post_type->name === 'acf' )
            continue;
        
        $dts_settings_post_type_field = function() use ( $post_type ) {
            $options = get_option( 'dts_settings' );
            $setting_name = 'dts_post_types_' . $post_type->name;
            
            if ( empty( $options ) ) 
            	$options = array();

            if ( ! isset( $options[$setting_name] ) ) :
            	if ( $post_type->name === 'post' || $post_type->name === 'page' )
            		$options[$setting_name] = '1';
            	else
	            	$options[$setting_name] = false;
            endif;

            $options[$setting_name] = isset( $options[$setting_name] ) ? $options[$setting_name] : false;
            ?>
            <input type="checkbox" name="dts_settings[<?= $setting_name; ?>]" value="1" <?php checked( $options[$setting_name], 1 ); ?> />
            <?php
        };

        add_settings_field( 'dts_post_types_' . $post_type->name, $post_type->labels->name, $dts_settings_post_type_field, 'dts_settings', 'dts_settings_post_types' );
    endforeach;
}
add_action( 'admin_init', 'dts_dbggr_init' );


/**
 * Validate plugin settings on save
 */
function dts_dbggr_settings_validate( $input ) {
    /* Add validations for data here. */
    return $input;
}


/**
 * Add DT's Debugger to Settings Menu
 */
function dts_dbggr_init_menu() {
    function dts_dbggr_options_page() {
        include( plugin_dir_path( __FILE__ ) . 'dts-settings.php' );
    }
    add_options_page( __( 'DT\'s Debugger', 'dts-debugger' ), __( 'DT\'s Debugger', 'dts-debugger' ), 'manage_options', 'dts-debugger', 'dts_dbggr_options_page' );
}
add_action( 'admin_menu', 'dts_dbggr_init_menu' );


/**
 * Add quicklinks column to posts and pages lists
 */
function dts_dbggr_post_modify_columns( $columns ) {
	
	$new_columns = array(
		'dts_quicklinks' => __('DT\'s Quicklinks', 'dts-debugger' )
	);

	$filtered_columns = array_merge( $columns, $new_columns );

	return $filtered_columns;
}


/**
 * Populate quicklinks column
 */
function dts_dbggr_custom_column_content( $column ) {

	global $post;
   	$options = get_option( 'dts_settings' );

	switch( $column ) :
		case 'dts_quicklinks':
			$debuggers = dts_dbggr_get_data();
		
		    foreach ( $debuggers as $debugger ) :

		        if ( ! is_string( $debugger ) ) :

		        	$setting_option = 'dts_debugger_' . $debugger['name'];

		        	if ( ! empty( $options ) && ( ! isset( $options[$setting_option] ) || $options[$setting_option] !== '1' ) )
		        		continue;

		            echo '<a href="' . $debugger['url'] . '" target="_blank" class="debug-btn column" title="' . __('Click to check with: ', 'dts-debugger' ) . __( $debugger['title'], 'dts-debugger' ) . '">';		           
		            echo '<img src="' . plugins_url( 'images/' . $debugger['image'], __FILE__ ) . '" alt="' . $debugger['title'] . '">';                      
		            echo '</a>';
		        endif;

		    endforeach;
		break;
	endswitch;
}


/** 
 * Scan for custom post types and init columns on init 
 */
function dts_dbggr_init_custom_columns() {
    $post_types = get_post_types( '', 'objects' );
    foreach ( $post_types as $post_type ) :

    	$options = get_option( 'dts_settings' );
    	$setting_option = 'dts_post_types_' . $post_type->name;

    	if ( empty( $options[$setting_option] ) || $options[$setting_option] !== '1' )
        	continue;

    	add_filter( 'manage_' . $post_type->name . '_posts_columns', 'dts_dbggr_post_modify_columns' );
		add_action( 'manage_' . $post_type->name . '_posts_custom_column', 'dts_dbggr_custom_column_content' );
    endforeach;
}
add_action( 'admin_init', 'dts_dbggr_init_custom_columns' );


/**
 * Add metabox to post/page editor
 */
function dts_dbggr_adding_metabox( $post_type, $post ) {
    $options = get_option( 'dts_settings' );
    $setting_option = 'dts_post_types_' . $post_type;

    if ( ! empty( $options[$setting_option] ) && $options[$setting_option] === '1')
        add_meta_box(   'sm-debug-post', __( 'DT\'s Debugger', 'dts-debugger' ),  'dts_dbggr_social_media_metabox', null, 'side', 'core' );
}

function dts_dbggr_social_media_metabox( $post ) {

    $options = get_option( 'dts_settings' );
    $setting_option = 'dts_post_types_' . $post->post_type;

    if ( empty( $options[$setting_option] ) || $options[$setting_option] !== '1' )
        die();

    $debuggers = dts_dbggr_get_data();

    echo '<div class="debug-wrapper">';

    foreach ( $debuggers as $debugger ) :

        if ( is_string( $debugger ) ) :
            echo '<h3 class="debug-btn-title">' . $debugger . ':</h3>';
       
        else :

        	$setting_option = 'dts_debugger_' . $debugger['name'];

        	if ( ! empty( $options ) && ( ! isset( $options[$setting_option] ) || $options[$setting_option] !== '1' ) )
        		continue;

            echo '<div class="debug-btn">';
            echo '<a href="' . $debugger['url'] . '" target="_blank" class="debug-btn" title="' . __('Click to check with: ', 'dts-debugger' ) . __( $debugger['title'], 'dts-debugger' ) . '">';
            echo '<img src="' . plugins_url( 'images/' . $debugger['image'], __FILE__ ) . '" alt="' . $debugger['title'] . '">';
            
            _e( $debugger['title'], 'dts-debugger' );
            
            echo '</a>';
            echo '</div>';
        endif;
    endforeach;

    echo '</div>';
}
add_action( 'add_meta_boxes', 'dts_dbggr_adding_metabox', 10, 2 );


/**
 * Internal data
 */
function dts_dbggr_get_data() {

    global $post;

    if ( ! isset( $post ) || ! isset( $post->ID ) )
        $permalink = 'javascript:;';
    else
    	$permalink = rawurlencode(get_permalink($post->ID));
   
    $debuggers = array(

        'Social Media',

        array(
        	'name'	=> 'facebook',
            'url'   => 'https://developers.facebook.com/tools/debug/og/object?q=' . $permalink,
            'title' => 'Open Graph Object Debugger',
            'image' => 'facebook.png'
        ),
        array(
        	'name'	=> 'google',
            'url'   => 'https://search.google.com/structured-data/testing-tool/u/0/?hl=en#url=' . $permalink,
            'title' => 'Structured Data Testing Tool',
            'image' => 'google.png'
        ),
        
        'Performance',

        array(
        	'name'	=> 'pagespeed',
            'url'   => 'https://developers.google.com/speed/pagespeed/insights/?url=' . $permalink,
            'title' => 'PageSpeed Insights',
            'image' => 'pagespeed.png'
        ),
        array(
        	'name'	=> 'w3c',
            'url'   => 'https://validator.w3.org/nu/?doc=' . $permalink,
            'title' => 'Nu Html Checker (W3C)',
            'image' => 'w3c.png'
        ),

        'Specialized',

        array(
            'name'  => 'amp',
            'url'   => 'https://search.google.com/test/amp?url=' . $permalink,
            'title' => 'Google AMP Test',
            'image' => 'amp.png'
        )
    );

    return $debuggers;
}
?>
