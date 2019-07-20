<?php

/**
 * CiviCRM Caldera Forms Contribution Processor Class.
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_Contribution_Processor {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $plugin The plugin instance
	 */
	public $plugin;

	/**
	 * Contact link.
	 *
	 * @since 0.4.4
	 * @access protected
	 * @var string $contact_link The contact link
	 */
	protected $contact_link;

	/**
	 * The processor key.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_contribution';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.2
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Contribution', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM contribution to contact', 'cf-civicrm' ),
			'author' => 'Agileware',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/contribution/contribution_config.php',
			'post_processor' =>  [ $this, 'post_processor' ],
		];

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function post_processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		if ( ! empty( $form_values ) ) {
			$form_values['financial_type_id'] = $config['financial_type_id']; // Financial Type ID

			if ( empty( $form_values['source'] ) )
				$form_values['source'] = $form['name'];

				$form_values['contact_id'] = $transient->contacts->{$this->contact_link}; // Contact ID

			$credit_card_id = civicrm_api3( 'OptionValue', 'get', [
				'sequential' => 1,
				'option_group_id.name' => 'payment_instrument',
				'name' => 'Credit Card',
			] );

			if ( $credit_card_id['count'] > 0 )
				$form_values['payment_instrument_id'] = $credit_card_id["values"][0]["value"];

			if ( isset( $form_values['is_pay_later'] ) && $form_values['is_pay_later'] )
				$form_values['contribution_status_id'] = 2; // set status to Pending (pay later)

			try {
				$create_contribution = civicrm_api3( 'Contribution', 'create', $form_values );
			} catch ( CiviCRM_API3_Exception $e ) {
				$error = $e->getMessage() . "<br><br><pre>" . $e->getTraceAsString() . "</per>";
				return [ 'note' => $error, 'type' => 'error' ];
			}
		}
	}
}
