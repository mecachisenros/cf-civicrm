<?php

/**
 * CiviCRM Caldera Forms Note Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Note_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_note';

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
			'name' => __( 'CiviCRM Note', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM note to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/note/note_config.php',
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

		$form_values = array();
		foreach ( $config as $key => $field_id ) {
			$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
			if( ! empty( $mapped_field ) ){
				$form_values[$key] = $mapped_field;
			}
		}

		if ( ! empty( $form_values ) ) {
			$form_values['entity_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

			// Add Note to contact
			$note = civicrm_api3( 'Note', 'create', $form_values );
		}
	}
}
