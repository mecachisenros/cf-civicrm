<?php

/**
 * CiviCRM Caldera Forms Dummy Processor Class.
 *
 * This is a boilerplate for creating new processors.
 *
 * @see https://github.com/Desertsnowman/cf-formprocessor-boilerplate
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Dummy_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_dummy';

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.2
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		// define processor
		$processors[$this->key_name] = array(

			// Required: Processor name
			'name' => __( 'Processor Name', 'caldera-forms-civicrm' ),

			// Required: Processor description
			'description' => __( 'Processor Description', 'caldera-forms-civicrm' ),

			// Optional: Icon / Logo displayed in processors picker modal
			'icon' => CF_CIVICRM_INTEGRATION_URL . 'assets/dummy_icon.png',

			// Optional: Author name
			'author' => 'Processor Author',

			// Optional: Author URL
			'author_url' => 'http://example.com/my_processor',

			// Optional: Pre-processor function used to verify and check data, can stop processing and return to user
			'pre_processor' => array( $this, 'pre_processor' ),

			// Optional: Processor function used to handle data, cannot stop processing. Returned data saved as entry meta
			'processor' => array( $this, 'processor' ),

			// Optional: Post-processor function used to cleanup or capture data from processing
			'post_processor' => array( $this, 'post_processor' ),

			// Optional: Config template for setting up the processor in form builder
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/dummy_config.php',

			// Optional: template for displaying meta data returned from processor function
			'meta_template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/dummy_meta.php',

			// Optional: default true: setting false will disable conditionals for the processor (use always)
			'conditionals' => true,

			// Optional: default false: setting as true will only allow once per form
			'single' => false,

			// Optional: Array of values processor returns to be used in magic tag autocomplete list
			'magic_tags' => array(

				// Adds {processor_slug:returned_tag} to magic tags
				'returned_tag',

				// Adds {processor_slug:another_returned} to magic tags etc..
				'another_returned',

			),

			// Optional: Array of WordPress script handle / urls to javascript files used in form builder
			'scripts' => array(

				// jQuery is already included, this is just an example of a handle
				'jquery',

			),

			// Optional: Array of WordPress style handle / urls to stylesheet files used in form builder
			'styles' => array(

				// doesn't exist, but just an example of a style url
				plugin_dir_url(__FILE__) . 'assets/css/style.css',

			),

		);

		return $processors;

	}

	/**
	 * Form pre-processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function pre_processor( $config, $form ) {

		// globalised transient object
		global $transdata;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form ) {

		// globalised transient object
		global $transdata;

	}

	/**
	 * Form post-processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function post_processor( $config, $form ) {

		// globalised transient object
		global $transdata;

	}

}
