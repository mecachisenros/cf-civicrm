<?php
/**
 * Plugin Name: Caldera Forms CiviCRM
 * Description: CiviCRM integration for Caldera Forms.
 * Version: 0.1.1
 * Author: Andrei Mondoc
 */

/**
 * Define constants.
 *
 * @since 0.1
 */
define( 'CF_CIVICRM_INTEGRATION_VER', '0.1.1' );
define( 'CF_CIVICRM_INTEGRATION_URL', plugin_dir_url( __FILE__ ) );
define( 'CF_CIVICRM_INTEGRATION_PATH', plugin_dir_path( __FILE__ ) );

/**
 * CiviCRM Caldera Forms Class.
 *
 * A class that encapsulates this plugin's functionality.
 *
 * @since 0.1.1
 */
class CiviCRM_Caldera_Forms {

	/**
	 * The class instance.
	 *
	 * @since 0.1.1
	 * @access private
	 * @var object $instance The class instance
	 */
	private static $instance;

	/**
	 * Returns a single instance of this object when called.
	 *
	 * @since 0.1.1
	 *
	 * @return object $instance CiviCRM_Caldera_Forms instance
	 */
	public static function instance() {

		// do we have it?
		if ( ! isset( self::$instance ) ) {

			// instantiate
			self::$instance = new CiviCRM_Caldera_Forms;

			// initialise
			self::$instance->includes();
			self::$instance->register_hooks();

			/**
			 * Broadcast to other plugins that this plugin is loaded.
			 *
			 * @since 0.1.1
			 */
			do_action( 'caldera_forms_civicrm_loaded' );

		}

		// always return instance
		return self::$instance;

	}

	/**
	 * Include plugin files.
	 *
	 * @since 0.1.1
	 */
	private function includes() {

		// Include plugin functions file
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/functions.php';

	}

	/**
	 * Register the hooks that our plugin needs.
	 *
	 * @since 0.1.1
	 */
	private function register_hooks() {

		// Hook to register CiviCRM Integration add-on
		add_filter( 'caldera_forms_get_form_processors', 'cf_civicrm_register_processor' );

		// FIXME
		// Add example forms
		// add_filter( 'caldera_forms_get_form_templates', 'cf_civicrm_template_examples' );

	}

}

/**
 * Instantiate plugin.
 *
 * @since 0.1.1
 *
 * @return object $instance The plugin instance
 */
function caldera_forms_civicrm() {
	return CiviCRM_Caldera_Forms::instance();
}

// init Caldera Forms CiviCRM
add_action( 'init', 'caldera_forms_civicrm' );
