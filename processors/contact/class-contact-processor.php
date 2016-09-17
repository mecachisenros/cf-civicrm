<?php

/**
 * CiviCRM Caldera Forms Contact Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Contact_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_contact';

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
			'name' =>  __( 'CiviCRM Contact', 'caldera-forms-civicrm' ),
			'description' =>  __( 'Create CiviCRM contact', 'caldera-forms-civicrm' ),
			'author' =>  'Andrei Mondoc',
			'template' =>  CF_CIVICRM_INTEGRATION_PATH . 'processors/contact/contact_config.php',
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

		// Get form values for each processor field
		// $value is the field id
		$form_values = array();
		foreach ( $config as $key => $field_id ) {
			$form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
		}

		// Set Contact type and sub-type from prcessor config
		$form_values['contact_type'] = $config['contact_type'];
		$form_values['contact_sub_type'] = $config['contact_sub_type'];

		// Indexed array containing the Email processors
		$civicrm_email_pr = Caldera_Forms::get_processor_by_type( 'civicrm_email', $form );
		if ( $civicrm_email_pr ) {
			foreach ( $civicrm_email_pr as $key => $value ) {
				if ( ! is_int( $key ) ) {
					unset( $civicrm_email_pr[$key] );
				}
			}
		}

		// FIXME Add Email processor option to set Defaul email for deduping?
		// Override Contact processor email address with first Email processor
		if ( $civicrm_email_pr ) {
			foreach ( $civicrm_email_pr[0]['config'] as $field => $value ) {
				if ( $field === 'email' ) {
					$form_values[$field] = $transdata['data'][$value];
				}
			}
		}

		// Dupes params
		$dedupeParams = CRM_Dedupe_Finder::formatParams( $form_values, $config['contact_type'] );
		$dedupeParams['check_permission'] = FALSE;

		// Check dupes
		$ids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams, $config['contact_type'], NULL, array(), $config['dedupe_rule'] );

		// Pass contact id if found
		$form_values['contact_id'] = $ids ? $ids[0] : 0;

		// Unset 'group', for some reason Civi's Api errors if present
		// unset( $form_values['group'] );
		$create_contact = civicrm_api3( 'Contact', 'create', $form_values );

		// Set returned contact_id to $transdata for later use
		// $transdata['civicrm']['contact_id'] = $create_contact['id'];

		// Store $cid
		CiviCRM_Caldera_Forms_Helper::set_civi_transdata( $config['contact_link'], $create_contact['id'] );
		$transdata['civicrm'] = CiviCRM_Caldera_Forms_Helper::get_civi_transdata();

	}

}
