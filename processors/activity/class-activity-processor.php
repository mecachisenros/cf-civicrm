<?php

/**
 * CiviCRM Caldera Forms Activity Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Activity_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_activity';

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
			'name' => __( 'CiviCRM Activity', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM activity to contact', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/activity/activity_config.php',
			'processor' =>  array( $this, 'processor' ),
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

		// Get form values
		$form_values = CiviCRM_Caldera_Forms_Helper::map_fields_to_processor( $config, $form, $form_values );

		if( ! empty( $form_values ) ) {
			$form_values['source_contact_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID set in Contact Processor
			$form_values['activity_type_id'] = $config['activity_type_id']; // Activity Type ID
			$form_values['status_id'] = $config['status_id']; // Activity Status ID
			$form_values['campaign_id'] = $config['campaign_id']; // Campaign ID

			// FIXME
			// Concatenate DATE + TIME
			// $form_values['activity_date_time'] = $form_values['activity_date_time'];

			$create_activity = civicrm_api3( 'Activity', 'create', $form_values );
		}
	}
}
