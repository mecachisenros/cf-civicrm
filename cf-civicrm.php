<?php
/**
 * Plugin Name: Caldera Forms CiviCRM - Agileware
 * Description: CiviCRM integration for Caldera Forms.
 * Version: 1.0.5-agileware-4
 * Author: Agileware
 * Author URI: https://github.com/agileware
 * Plugin URI: https://github.com/agileware/caldera-forms-civicrm
 * Text Domain: cf-civicrm
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/agileware/caldera-forms-civicrm
 */

/**
 * Define constants.
 *
 * @since 0.1
 */
define( 'CF_CIVICRM_INTEGRATION_VER', '1.0.5-agileware-4' );
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
	public $processors;

	/**
	 * The fields management object.
	 *
	 * @since 0.2
	 * @access public
	 * @var object $fields The fields management object
	 */
	public $fields;

	/**
	 * The templates management object.
	 *
	 * @since 0.3
	 * @access public
	 * @var object $templates The templates management object
	 */
	public $templates;

	/**
	 * The form management object.
	 *
	 * @since 0.4
	 * @access public
	 * @var object $templates The form management object
	 */
	public $forms;

	/**
	 * The entries management object.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var object $entries The entries management object
	 */
	public $entries;

	/**
	 * The ajax management object.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var object $entries The entries management object
	 */
	public $ajax;

	/**
	 * The helper management object.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $helper The helper management object
	 */
	public $helper;

	/**
	 * The assets management object.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $helper The helper management object
	 */
	public $assets;

	/**
	 * The transient management object.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $transient The helper management object
	 */
	public $transient;

	/**
	 * CiviCRM API wrapper management object.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $api The CiviCRM API wrapper object
	 */
	public $api;

	/**
	 * CiviCRM html object.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $html The html object
	 */
	public $html;

	/**
	 * CiviDiscount helper object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $cividiscount The CiviDiscount helper object
	 */
	public $cividiscount;

	/**
	 * Define the maximum number of CiviCRM Contacts
	 *
	 * @since 1.0
	 * @access public
	 * @var int $maxcontacts
	 */
	public $maxcontacts;

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
		if ( ! defined( 'CFCORE_VER' ) || ! version_compare( CFCORE_VER, '1.8.1', '>=' ) ) {
			add_action( 'admin_notices', [$this, 'caldera_forms_version_notice'] );
			return false;
		}

		// Bail if CiviCRM is not available
		if ( ! function_exists( 'civi_wp' ) ) return false;

		// Bail if unable to init CiviCRM
		// FIXME This should only be called when needed
		if ( ! civi_wp()->initialize() ) return false;

		// we're good
		return true;

	}

	/**
	 * Include plugin files.
	 *
	 * @since 0.1.1
	 */
	private function include_files() {
		// Include api wrapper class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-crm-api.php';
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
		// Include entries management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-entries.php';
		// Include ajax management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-ajax.php';
		// Include assets management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-assets.php';
		// Include assets management class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-transient.php';
		// Include html class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-html.php';
		// include CiviDiscount helper class
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-civicrm-caldera-forms-cividiscount.php';

	}

	/**
	 * Set up plugin objects.
	 *
	 * @since 0.2
	 */
	private function setup_objects() {

		// init api wrapper class
		$this->api = new CiviCRM_Caldera_Forms_CRM_API( $this );
		// init helper class
		$this->helper = new CiviCRM_Caldera_Forms_Helper( $this );
		// init transient manager
		$this->transient = new CiviCRM_Caldera_Forms_Transient( $this );
		// init processors manager
		$this->processors = new CiviCRM_Caldera_Forms_Processors( $this );
		// init fields manager
		$this->fields = new CiviCRM_Caldera_Forms_Fields( $this );
		// init templates manager
		$this->templates = new CiviCRM_Caldera_Forms_Templates( $this );
		// init forms manager
		$this->forms = new CiviCRM_Caldera_Forms_Forms( $this );
		// init entries manager
		$this->entries = new CiviCRM_Caldera_Forms_Entries( $this );
		// init ajax manager
		$this->ajax = new CiviCRM_Caldera_Forms_AJAX( $this );
		// init assets manager
		$this->assets = new CiviCRM_Caldera_Forms_Assets( $this );
		// init html class
		$this->html = new CiviCRM_Caldera_Forms_HTML( $this );
		// init cividiscount class
		if ( $this->processors->enabled_extensions && in_array( 'org.civicrm.module.cividiscount', $this->processors->enabled_extensions ) )
			$this->cividiscount = new CiviCRM_Caldera_Forms_CiviDiscount( $this );

		// @TODO Expose this as a Caldera Forms CiviCRM Setting
		$this->maxcontacts = 30;
	}

	/**
	 * Register the hooks that our plugin needs.
	 *
	 * @since 0.1.1
	 */
	private function register_hooks() {

		// use translation files
		add_action( 'plugins_loaded', [ $this, 'enable_translation' ] );

	}

	/**
	 * Cladera Forms version notice.
	 *
	 * @since 0.4.4
	 */
	public function caldera_forms_version_notice() {
		?>
			<div class="notice notice-error">
				<p><?php _e( 'Caldera Forms CiviCRM requires Caldera Forms v1.8.1 or higher.', 'cf-civicrm' ); ?></p>
			</div>
		<?php
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
			'cf-civicrm', // unique name
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
