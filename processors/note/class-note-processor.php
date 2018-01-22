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
			'pre_processor' => array( $this, 'pre_processor' ),
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
	public function pre_processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		// Get form values
		$form_values = CiviCRM_Caldera_Forms_Helper::map_fields_to_processor( $config, $form, $form_values );

		if ( ! empty( $form_values ) ) {
			$form_values['entity_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

			// Add Note to contact
			$note = CiviCRM_Caldera_Forms_Helper::try_crm_api( 'Note', 'create', $form_values );

			// handle attachment using CRM_Core_DAO_EntityFile
			if ( ! empty( $config['note_attachment'] ) ) {

				$transdata['civicrm']['civicrm_files'] = CiviCRM_Caldera_Forms_Helper::get_file_entity_ids();

				foreach ( $transdata['civicrm']['civicrm_files'] as $field_number => $file ) {
					if ( $config['note_attachment'] == $file['field_id'] && ! empty( $file['file_id'] ) ) {

						CiviCRM_Caldera_Forms_Helper::create_civicrm_entity_file( 'civicrm_note', $note['id'], $file['file_id'] );

					}
				}
			}
		}
	}
}
