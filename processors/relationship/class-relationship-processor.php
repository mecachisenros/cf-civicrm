<?php

/**
 * CiviCRM Caldera Forms Relationship Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Relationship_Processor {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_relationship';

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );

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

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Relationship', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM relationship to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/relationship/relationship_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
		];

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

		// cfc transient object
		$transient = $this->plugin->transient->get();

		$relationship = civicrm_api3( 'Relationship', 'get', [
			'sequential' => 1,
			'contact_id_a' => $transient->contacts->{'cid_'.$config['contact_a']},
			'contact_id_b' => $transient->contacts->{'cid_'.$config['contact_b']},
			'relationship_type_id' => $config['relationship_type'],
		] );

		if ( $relationship['count'] ) {
			return;
		} else {
			try {
				$create_relationship = civicrm_api3( 'Relationship', 'create', [
					'sequential' => 1,
					'contact_id_a' => $transient->contacts->{'cid_'.$config['contact_a']},
					'contact_id_b' => $transient->contacts->{'cid_'.$config['contact_b']},
					'relationship_type_id' => $config['relationship_type'],
				] );
			} catch ( CiviCRM_API3_Exception $e ) {
				$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
				return [ 'note' => $error, 'type' => 'error' ];
			}
		}

	}

}
