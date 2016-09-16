<?php

/**
 * CiviCRM Caldera Forms Processors Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Processors {

	/**
	 * The processor objects.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $processors An array that hold all processor objects
	 */
	public $processors;

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

	}

	/**
	 * Include processor files.
	 *
	 * @since 0.2
	 */
	private function include_files() {

		// Include processor classes
		include CF_CIVICRM_INTEGRATION_PATH . 'includes/class-contact-processor.php';

	}

	/**
	 * Set up processor objects.
	 *
	 * @since 0.2
	 */
	private function setup_objects() {

		// add processors to array
		$this->processors['civicrm_contact'] = new CiviCRM_Caldera_Forms_Contact_Processor;

	}

	/**
	 * Register any hooks.
	 *
	 * @since 0.1.1
	 */
	private function register_hooks() {

	}

}
