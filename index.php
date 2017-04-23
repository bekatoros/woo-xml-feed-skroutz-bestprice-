<?php
/* Plugin Name: Skroutz.gr & Bestprice.gr XML Feed for Woocommerce
Plugin URI: http://emspace.gr/
Description: XML feed creator for Skroutz & Best Price
Version: 1.0.11
Author: emspace.gr
Author URI: http://emspace.gr/
License: GPLv3 or later
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
load_plugin_textdomain('skroutz-woocommerce-feed', false, dirname(plugin_basename(__FILE__)) . '/languages/');

function skroutz_xml_admin_menu() {

    /* add new top level */
    add_menu_page(
		__( 'Skroutz & BestPrice', 'skroutz-woocommerce-feed' ),
		__( 'Skroutz & BestPrice', 'skroutz-woocommerce-feed' ),
		'manage_options',
		'skroutz_xml_admin_menu',
		'skroutz_xml_admin_page',
		plugins_url( '/', __FILE__ ) . '/images/xml-icon.png'
	);

	/* add the submenus */
    add_submenu_page(
		'skroutz_xml_admin_menu',
		__( 'Create Feeds', 'skroutz-woocommerce-feed' ),
		__( 'Create Feeds', 'skroutz-woocommerce-feed' ),
		'manage_options',
		'skroutz_xml_create_page',
		'skroutz_xml_create_page'
	);

    

}
add_action( 'admin_menu', 'skroutz_xml_admin_menu' );
add_action( 'admin_init', 'register_mysettings' );

function skroutz_xml_admin_page() 
{

add_action( 'wp', 'skroutz_xml_setup_schedule' );
	$skicon	= plugins_url( '/', __FILE__ ) . '/images/skroutz.png';
	$bpicon	= plugins_url( '/', __FILE__ ) . '/images/bp.png';
	echo 	'<div><img src="'.$skicon.'" height="150px"> <img src="'.$bpicon.'" height="150px">';
	
	
	echo 	'<h2>Create Feeds for Skroutz.gr and bestprice.gr</h2>';
	echo    '</div>';
	
	global $woocommerce;
$attribute_taxonomies = wc_get_attribute_taxonomies();

	
echo	'<form method="post" action="options.php">'; 
	settings_fields( 'skroutz-group' );
	do_settings_sections( 'skroutz-group' );
echo 	'<table class="form-table">
        <tr valign="top">
        <th scope="row">'.__('When in Stock Availability', 'skroutz-woocommerce-feed').'</th><td>';
      

$options = get_option('instockavailability');
	$items = array(__('Άμεση παραλαβή / Παράδoση 1 έως 3 ημέρες', 'skroutz-woocommerce-feed'), __('Παράδοση σε 1 - 3 ημέρες', 'skroutz-woocommerce-feed'), __('Παράδοση σε 4 - 10 ημέρες', 'skroutz-woocommerce-feed'));
	echo "<select id='drop_down1' name='instockavailability'>";
	foreach($items as $key=>$item) {
		$selected = ($options['instockavailability']==$key) ? 'selected="selected"' : '';
		echo "<option value='".esc_html($key)."' $selected>".esc_html($item)."</option>";
	}
	echo "</select>";
	echo 	'</td>
        </tr>
         
        </tr>
        
        <tr valign="top">
        <th scope="row">'.__('If a Product is out of Stock', 'skroutz-woocommerce-feed').'</th>
        <td>';	
	
	$options2 = get_option('ifoutofstock');
	$items = array(__('Include as out of Stock or Upon Request', 'skroutz-woocommerce-feed'), __('Exclude from feed', 'skroutz-woocommerce-feed'));
	echo "<select id='drop_down2' name='ifoutofstock'>";
	foreach($items as $key=>$item) {
		$selected = ($options2['ifoutofstock']==$key) ? 'selected="selected"' : '';
		echo "<option value='".esc_html($key)."' $selected>".esc_html($item)."</option>";
	}
	echo "</select>";		
	echo 	'</td>        </tr>   ';
	echo     '  </tr>';
	
	echo '	        <tr valign="top">
        <th scope="row">'.__('Features for bestprice', 'skroutz-woocommerce-feed').'</th><td>';
      

 $options3= get_option('features');
	echo "<select id='drop_down3' name='features[]' multiple='multiple'>";
	foreach($attribute_taxonomies as $tax) {
	$selected = false;
		if( in_array(  $tax->attribute_id,$options3 )	) {
			$selected = true;			
			} 

		echo "<option value='".esc_html($tax->attribute_id)."' " . selected( $selected, true, false ) . ">".esc_html($tax->attribute_label)."</option>";
	}
	echo "</select>";
	echo 	'</td>
        </tr>';
	echo ' </table>';
	submit_button(); 
	echo '</form></div>';
	
	
	echo '<a class="button button-primary" href="'.get_admin_url().'admin.php?page=skroutz_xml_create_page">'.__('Create XML Feeds', 'skroutz-woocommerce-feed').'</a>';
	



	
}

function register_mysettings() { // whitelist options
  register_setting( 'skroutz-group', 'instockavailability',sanitize_options );
  register_setting( 'skroutz-group', 'ifoutofstock',sanitize_options );
  register_setting( 'skroutz-group', 'features',sanitize_options_multi );
 
}


function sanitize_options($input) {

        return  esc_html($input);
} 

function sanitize_options_multi($input) {

$output = array();

foreach ($input as $in_value)
{
$output[] = esc_html ($in_value);
}

		
        return $output;
}

function skroutz_xml_create_page() 
{

	$skicon	= plugins_url( '/', __FILE__ ) . '/images/skroutz.png';
	$bpicon	= plugins_url( '/', __FILE__ ) . '/images/bp.png';
	echo 	'<div><img src="'.$skicon.'" height="150px"> <img src="'.$bpicon.'" height="150px">';
	echo 	'<h2>'.__('Create Feeds for Skroutz.gr and bestprice.gr', 'skroutz-woocommerce-feed').'</h2>';
	echo    '</div>';	

	settings_fields( 'skroutz-group' );
	do_settings_sections( 'skroutz-group' );
	
	$active = 0; // get_option('activefeeds');
	if($active == 0 |  $active== 1){
	require_once 'createsk.php';
	}
	echo '</br>';	
	if($active == 0 |  $active== 2){
	require_once 'createbp.php';
	}
	if ( ! wp_next_scheduled( 'skroutz_xml_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'skroutz_xml_hourly_event');
	}
}





add_action( 'skroutz_xml_hourly_event', 'skroutz_xml_do_this_hourly' );
/**
 * On the scheduled action hook, run a function.
 */
function skroutz_xml_do_this_hourly() {
	// do something every hour
	
	
	$active = 0; // get_option('activefeeds');
	if($active == 0 |  $active== 1){
	require_once 'createsk.php';
	}		
	if($active == 0 |  $active== 2){
	require_once 'createbp.php';
	}
	
	if ( ! wp_next_scheduled( 'skroutz_xml_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'skroutz_xml_hourly_event');
	}
}

?>