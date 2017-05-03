<?php
/**
 * Plugin Name: Caldera Forms CiviCRM
 * Description: CiviCRM integration for Caldera Forms.
 * Version: 0.4
 * Author: Andrei Mondoc
 * Author URI: https://github.com/mecachisenros
 * Plugin URI: https://github.com/mecachisenros/caldera-forms-civicrm
 * Text Domain: caldera-forms-civicrm
 * Domain Path: /languages
 */

/**
 * Define constants.
 *
 * @since 0.1
 */
define( 'CF_CIVICRM_INTEGRATION_VER', '0.4' );
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
	 * The processor management object.
	 *
	 * @since 0.2
	 * @access public
	 * @var object $processors The processor management object
	 */
	public static $processors;

	/**
	 * The fields management object.
	 *
	 * @since 0.2
	 * @access public
	 * @var object $fields The fields management object
	 */
	public static $fields;

	/**
	 * The templates management object.
	 *
	 * @since 0.3
	 * @access public
	 * @var object $templates The templates management object
	 */
	public static $templates;

	/**
	 * The form management object.
	 *
	 * @since 0.4
	 * @access public
	 * @var object $templates The form management object
	 */
	public static $forms;

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

			// initialise if the environment allows
			if ( self::$instance->check_dependencies() ) {
				self::$instance->include_files();
				self::$instance->setup_objects();
				self::$instance->register_hooks();
			}

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
	 * Check our plugin dependencies.
	 *
	 * @since 0.2
	 *
	 * @return bool True if dependencies exist, false otherwise
	 */
	private function check_dependencies() {

		// Bail if Caldera Forms is not available
		if ( ! defined( 'CFCORE_VER' ) && version_compare( CFCORE_VER, '1.5', '>=' ) ) return false;

		// Bail if CiviCRM is not available
		if ( ! function_exists( 'civi_wp' ) ) return false;

		// Bail if unable to init CiviCRM
		// FIXME This should only be called when needed
		if ( ! civi_wp()->initialize() ) return $processors;

		// we're good
		return true;

	}

	/**
	 * Include plugin files.
	 *
	 * @since 0.1.1
	 */
	private function include_files() {

		// Include helper class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-helper.php';

		// Include processor management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-processors.php';

		// Include field management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-fields.php';

		// Include template management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-templates.php';

		// Include forms management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-forms.php';

	}

	/**
	 * Set up plugin objects.
	 *
	 * @since 0.2
	 */
	private function setup_objects() {

		// init processors manager
		self::$processors = new CiviCRM_Caldera_Forms_Processors;

		// init fields manager
		self::$fields = new CiviCRM_Caldera_Forms_Fields;

		// init templates manager
		self::$templates = new CiviCRM_Caldera_Forms_Templates;

		// init forms manager
		self::$forms = new CiviCRM_Caldera_Forms_Forms;

	}

	/**
	 * Register the hooks that our plugin needs.
	 *
	 * @since 0.1.1
	 */
	private function register_hooks() {

		// use translation files
		add_action( 'plugins_loaded', array( $this, 'enable_translation' ) );

	}

	/**
	 * Load translation files.
	 *
	 * A good reference on how to implement translation in WordPress:
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 *
	 * @since 0.1.1
	 */
	public function enable_translation() {

		// load translations if present
		load_plugin_textdomain(
			'caldera-forms-civicrm', // unique name
			false, // deprecated argument
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' // relative path to translation files
		);

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
