<?php
/*
Plugin Name:  Practice
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  This plugin will show the QR Code for every post
Version:      1.0.0
Author:       Hasib
Author URI:   https://hasib.me/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  practice
Domain Path:  /languages/
*/

$pr_countries = array(

    __('Afganistan','practice'),
    __('Bangladesh','practice'),
    __('Bhutan','practice'),
    __('India','practice'),
    __('Maldives','practice'),
    __('Nepal','practice'),
    __('Pakistan','practice'),
    __('Srilanka','practice')

);

function pr_init(){
    global $pr_countries;
    $pr_countries = apply_filters('pr_countries',$pr_countries);
}
add_action('init','pr_init');

function pr_text_domain(){
    load_plugin_textdoman('practice',false, dirname(__FILE__). '/languages');
}
add_action('pugins_loaded','pr_text_domain');

function pr_qrcode_display($content){
    $current_post_id = get_the_ID();
    $current_post_title = get_the_title($current_post_id);
    $current_post_type = get_post_type($current_post_id);
    $current_post_url = urlencode( get_the_permalink($current_post_id) );

    /**
     * Post Type Check
     */
    $excluded_post_types = apply_filters('pr_excluded_post_type',array());
    if(in_array($current_post_type,$excluded_post_types)){
        return $content;
    }
    
    /**
     * Dimension hook
     */
    $height = get_option('pr_height');
    $width = get_option('pr_width');
    $height = $height?$height:300;
    $width = $width?$width:300;
    $dimension = apply_filters('pr_dimension', "{$height}x{$width}");

    /**
     * Disply QR Code
     */
    $image_url = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=%s&data=%s',$dimension,$current_post_url);
    $content .= sprintf('<img src="%s" alt="%s"/>',$image_url,$current_post_title);
    return $content;
}
add_filter('the_content','pr_qrcode_display');

function pr_setting_init(){

    add_settings_section('pr_setting_section', __('QR CODE SECTION','practice'), 'pr_section_callback', 'general');
    
    add_settings_field( 'pr_height', __('QR CODE HEIGHT','practice'), 'pr_display_field', 'general', 'pr_setting_section', array('pr_height') );
    add_settings_field( 'pr_width', __('QR CODE WIDTH','practice'), 'pr_display_field', 'general', 'pr_setting_section', array('pr_width') );
    add_settings_field( 'pr_dropdown', __('DROPDOWN','practice'), 'pr_display_select_field', 'general', 'pr_setting_section' );
    add_settings_field( 'pr_checkbox', __('Select Countries','practice'), 'pr_display_checkbox_group_field', 'general', 'pr_setting_section' );
    add_settings_field( 'pr_toggle', __('Toggle UI','practice'), 'pr_toggle_field', 'general', 'pr_setting_section' );
    

    register_setting('general', 'pr_height', array('sanitize_callback'  => 'esc_attr'));
    register_setting('general', 'pr_width', array('sanitize_callback'  => 'esc_attr'));
    register_setting('general', 'pr_dropdown', array('sanitize_callback'  => 'esc_attr'));
    register_setting('general', 'pr_checkbox');
    register_setting('general', 'pr_toggle');
    
    
}

function pr_toggle_field(){
    echo '<div class="toggle"></div>';
}




function pr_display_checkbox_group_field(){

    global $pr_countries;

    $options = get_option('pr_checkbox');

    foreach($pr_countries as $countryName){
        $checked = '';
        if(is_array($options) && in_array($countryName,$options)){
            $checked = 'checked';
        }
        printf('<input type = "checkbox" name="%s" value="%s" %s />%s</br>','pr_checkbox[]',$countryName,$checked,$countryName);
    }
}

function pr_display_select_field(){

    global $pr_countries;

    $option = get_option('pr_dropdown');

    printf('<select id="%s" name="%s">','pr_dropdown','pr_dropdown');

    foreach($pr_countries as $country){
        $selected = '';
        if($option == $country){
            $selected = 'selected';
        }
        printf('<option value="%s" %s>%s</option>',$country,$selected,$country);
    }
    echo '</select>';

}

function pr_display_field($args){
    $option = get_option($args[0]);
    printf('<input type="text" id="%s" name="%s" value="%s"/>',$args[0],$args[0],$option);
}

function pr_section_callback(){
    echo '<p>'.__('setting for QR-CODE','practice').'</p>';
}

add_action('admin_init','pr_setting_init');

function pr_asset_enqueue($screen){
    if( 'options-general.php' == $screen ) {

        wp_enqueue_style('pr-minitoggle-css',plugin_dir_url(__FILE__).'assets/css/minitoggle.css');
        wp_enqueue_script('pr-minitoggle-js',plugin_dir_url(__FILE__).'/assets/js/minitoggle.js',array('jquery'),'1.0',true);
        wp_enqueue_script('pr-main-js', plugin_dir_url(__FILE__).'assets/js/pr-main.js',array('jquery'),time(),true);
    }
}
add_action('admin_enqueue_scripts','pr_asset_enqueue');
