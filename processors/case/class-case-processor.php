<?php

/**
 * CiviCRM Caldera Forms Case Processor Class.
 *
 * @since 0.4.1
 */
class CiviCRM_Caldera_Forms_Case_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.4.1
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_case';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.1
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.1
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Case', 'caldera-forms-civicrm' ),
			'description' => __( 'Add/Open CiviCRM Case (CiviCase) to contact', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/case/case_config.php',
			'processor' =>  array( $this, 'processor' ),
			'magic_tags' => array(
				"case_id"
			),
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.1
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		// Get form values
		$form_values = CiviCRM_Caldera_Forms_Helper::map_fields_to_processor( $config, $form, $form_values );

		if ( $config['dismiss_case'] ) {
			$existing_cases = civicrm_api3('Case', 'get', array(
  				'sequential' => 1,
  				'contact_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']],
  				'case_type_id' => $config['case_type_id'],
  				'options' => array('limit' => 0),
			));
		}

		$form_values['contact_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID set in Contact Processor
		$form_values['case_type_id'] = $config['case_type_id']; // Case Type ID
		$form_values['case_status_id'] = $config['case_status_id']; // Case Status ID
		
		if ( ! empty( $config['creator_id'] ) ) {
			$form_values['creator_id'] = strpos( $config['creator_id'], 'contact_' ) !== false ? $transdata['civicrm']['contact_id_' . str_replace( 'contact_', '', $config['creator_id'] )] : $config['creator_id']; // Case Manager
		}

		if( empty( $config['start_date'] ) ){
			$form_values['start_date'] = date( 'YmdHis', strtotime( 'now' ) ); // Date format YYYYMMDDhhmmss
		}

		if( isset( $existing_cases ) && $existing_cases['count'] > 0 ){
			// don't create case
		} else {
			try {
				$create_case = civicrm_api3( 'Case', 'create', $form_values );
				return array(
					"case_id"  =>  $create_case["id"]
				);
			} catch (Exception $e) {

			}
		}

	}
}
