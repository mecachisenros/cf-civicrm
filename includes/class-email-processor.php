<?php

/**
 * CiviCRM Caldera Forms Email Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Email_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_email';

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

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Email', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM email to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/email_config.php',
			'processor' => array( $this, 'processor' ),
		);

		return $processors;

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

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$email = civicrm_api3( 'Email', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['location_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			$form_values = array();
			foreach ( $config as $key => $field_id ) {
				$form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
			}

			$form_values['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

			// Pass Email ID if we got one
			if ( $email ) {
				$form_values['id'] = $email['id']; // Email ID
			}

			$create_email = civicrm_api3( 'Email', 'create', $form_values );

		}

	}

}
