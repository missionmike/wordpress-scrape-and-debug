<?php
   /*
   Plugin Name: DT's Debugger
   Plugin URI: http://dtweb.design/debugger/
   Description: Simplify page debugging via Facebook Developer Tools, Google's Structured Data Testing Tool, PageSpeed Insights, W3C Validation (more to come). Found in page/post sidebar metabox.
   Version: 0.0.1
   Author: Michael R. Dinerstein
   Author URI: http://dtweb.design/
   License: GPL2
   */
   
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function dts_dbggr_action_links( $links ) {
    $links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=dts-debugger' ) ) . '">Settings</a>';
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dts_dbggr_action_links' );


function dts_dbggr_activate() {
    add_option( 'dts_settings' );
    add_option( 'dts_settings_activate_redirect', true );
}
register_activation_hook( __FILE__, 'dts_dbggr_activate' );


function dts_dbggr_activate_redirect( $plugin ) {
    if ( get_option( 'dts_settings_activate_redirect', false ) ) :
        delete_option( 'dts_settings_activate_redirect' );
        exit( wp_redirect( get_admin_url( null, 'options-general.php?page=dts-debugger' ) ) );  
    endif;
}
add_action( 'admin_init', 'dts_dbggr_activate_redirect' );


function dts_dbggr_remove() {
    delete_option( 'dts_settings' );
}
register_deactivation_hook( __FILE__, 'dts_dbggr_remove' );


function dts_dbggr_init() {
    load_plugin_textdomain('dts-debugger', false, basename( dirname( __FILE__ ) ) . '/languages' );

    register_setting( 'dts_settings', 'dts_settings', 'dts_dbggr_settings_validate' );
    add_settings_section( 'dts_settings_post_types', __( 'Post Types', 'dts-debugger' ), 'dts_dbggr_settings_post_types_text', 'dts_settings' );

    $post_types = get_post_types( '', 'objects' );
    foreach ( $post_types as $post_type ) :
        if ( $post_type->name === 'attachment' || $post_type->name === 'revision' || $post_type->name === 'nav_menu_item' || $post_type->name === 'acf' )
            continue;
        
        $dts_settings_post_type_field = function() use ( $post_type ) {
            $options = get_option( 'dts_settings' );
            $setting_name = 'dts_post_types_' . $post_type->name;
            $options[$setting_name] = isset($options[$setting_name]) ? $options[$setting_name] : false;
            ?>
            <input type="checkbox" name="dts_settings[<?= $setting_name; ?>]" value="1" <?php checked( $options[$setting_name], 1 ); ?> />
            <?php
        };

        add_settings_field( 'dts_post_types_' . $post_type->name, $post_type->labels->name, $dts_settings_post_type_field, 'dts_settings', 'dts_settings_post_types' );
    endforeach;
}
add_action( 'admin_init', 'dts_dbggr_init' );


function dts_dbggr_settings_validate( $input ) {
	/* DEV:

    $options = get_option( 'dts_settings' );

    $post_types = get_post_types( '', 'objects' );
    foreach ( $post_types as $post_type ) :
        if ( $post_type->name === 'attachment' || $post_type->name === 'revision' || $post_type->name === 'nav_menu_item' || $post_type->name === 'acf' )
            continue;

        $options['dts_post_types_' . $post_type->name] = $input['dts_post_types_' . $post_type->name];        
    endforeach;

	*/
    return $input;
}


function dts_dbggr_settings_post_types_text() {
    echo '<p>Select which post types display DT\'s Debugger panel:</p>';
}


function dts_dbggr_init_menu() {
    add_options_page( __( 'DT\'s Debugger', 'dts-debugger' ), __( 'DT\'s Debugger', 'dts-debugger' ), 'manage_options', 'dts-debugger', 'dts_dbggr_options_page' );
}
add_action( 'admin_menu', 'dts_dbggr_init_menu' );


function dts_dbggr_options_page() {
    include( plugin_dir_path( __FILE__ ) . 'dts-settings.php' );
}


function dts_dbggr_register_scripts() {
    $version = '08302016';

    wp_register_style( 'dts-style', plugins_url( 'css/styles.css', __FILE__ ), false, $version );
}
add_action( 'admin_init', 'dts_dbggr_register_scripts' );


function dts_dbggr_enqueue_scripts() {
    wp_enqueue_style( 'dts-style' );
}
add_action( 'admin_enqueue_scripts', 'dts_dbggr_enqueue_scripts' );


function dts_dbggr_adding_metabox( $post_type, $post ) {
    $options = get_option( 'dts_settings' );
    $setting_option = 'dts_post_types_' . $post_type;

    if ( $options[$setting_option] === '1')
        add_meta_box(   'sm-debug-post', __('DT\'s Debugger', 'dts-debugger'),  'dts_dbggr_social_media_metabox', Array('post', 'page', 'article', 'issue', 'promo', 'template'), 'side', 'core');
}


function dts_dbggr_social_media_metabox( $post ) {

    $options = get_option( 'dts_settings' );
    $setting_option = 'dts_post_types_' . $post->post_type;

    if ( $options[$setting_option] !== '1' )
        die();

    $permalink = rawurlencode(get_permalink($post->ID));
    $debug_items = array(
        'Social Media',

        'facebook'  => array(
            'url'   => 'https://developers.facebook.com/tools/debug/og/object?q=' . $permalink,
            'title' => 'Open Graph Object Debugger',
            'image' => 'facebook.png'
        ),
        'google'    => array(
            'url'   => 'https://search.google.com/structured-data/testing-tool/u/0/?hl=en#url=' . $permalink,
            'title' => 'Structured Data Testing Tool',
            'image' => 'google.png'
        ),
        
        'Performance',

        'pagespeed' => array(
            'url'   => 'https://developers.google.com/speed/pagespeed/insights/?url=' . $permalink,
            'title' => 'PageSpeed Insights',
            'image' => 'pagespeed.png'
        ),
        'w3c'       => array(
            'url'   => 'https://validator.w3.org/nu/?doc=' . $permalink,
            'title' => 'Nu Html Checker (W3C)',
            'image' => 'w3c.png'
        )
    );

    echo '<div class="debug-wrapper">';

    foreach ( $debug_items as $item ) :

        if ( is_string( $item ) ) :
            echo '<h3 class="debug-btn-title">' . $item . '</h3>';
        else :
            echo '<div class="debug-btn">';
            echo '<a href="' . $item['url'] . '" target="_blank" class="debug-btn">';
            echo '<img src="' . plugins_url( 'images/' . $item['image'], __FILE__ ) . '" alt="' . $item['title'] . '">';
            
            _e( $item['title'], 'dts-debugger' );
            
            echo '</a>';
            echo '</div>';
        endif;
    endforeach;

    echo '</div>';
}
add_action( 'add_meta_boxes', 'dts_dbggr_adding_metabox', 10, 2 );

?>
