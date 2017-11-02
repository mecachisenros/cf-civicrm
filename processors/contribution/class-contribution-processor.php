<?php

/**
 * CiviCRM Caldera Forms Contribution Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Contribution_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_contribution';

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
			'name' => __( 'CiviCRM Contribution', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM contribution to contact', 'caldera-forms-civicrm' ),
			'author' => 'Agileware',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/contribution/contribution_config.php',
			'post_processor' =>  array( $this, 'post_processor' ),
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
	public function post_processor( $config, $form) {

		// globalised transient object
		global $transdata;

		// Get form values
		$form_values = CiviCRM_Caldera_Forms_Helper::map_fields_to_processor( $config, $form, $form_values );

		if( ! empty( $form_values ) ) {
			$form_values['financial_type_id'] = $config['financial_type_id']; // Financial Type ID
			if($form_values["source"] == "") {
				$form_values["source"] = $form["name"];
			}
			$form_values['contact_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID
			$credit_card_id = civicrm_api3( 'OptionValue', 'get', array(
				'sequential'           => 1,
				'option_group_id.name' => 'payment_instrument',
				'name'                 => 'Credit Card',
			));
			if($credit_card_id["count"] > 0) {
				$form_values['payment_instrument_id'] = $credit_card_id["values"][0]["value"];
			}

			$create_contribution = civicrm_api3( 'Contribution', 'create', $form_values );
		}
	}
}
