<?php
/**
 * Plugin Name: Caldera Forms - CiviCRM Integration
 * Description: Civicrm Integration for Caldera Forms, requieres Cladera Forms and CiviCRM
 * Version: 0.1.1
 * Author: Andrei Mondoc
 */

/*
* Define constants
*/

define( 'CF_CIVICRM_INTEGRATION_VER', '0.1.0' );
define( 'CF_CIVICRM_INTEGRATION_URL',     plugin_dir_url( __FILE__ ) );
define( 'CF_CIVICRM_INTEGRATION_PATH',    dirname( __FILE__ ) . '/' );
define( 'CF_CIVICRM_INTEGRATION_CORE',    dirname( __FILE__ )  );

add_action('init', 'cf_civicrm_integration_init');

function cf_civicrm_integration_init(){
	
	// Include plugin functions
	include CF_CIVICRM_INTEGRATION_PATH . 'includes/functions.php';

	// Hook to register CiviCRM Integration add-on
	add_filter( 'caldera_forms_get_form_processors', 'cf_civicrm_register_processor' );
	
	// FIXME
	// Add example forms
	// add_filter( 'caldera_forms_get_form_templates', 'cf_civicrm_template_examples' );
}
