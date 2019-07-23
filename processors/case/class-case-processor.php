<?php

/**
 * CiviCRM Caldera Forms Case Processor Class.
 *
 * @since 0.4.1
 */
class CiviCRM_Caldera_Forms_Case_Processor {

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// register case custom fields for autopopulate/presets
		add_filter( 'cfc_custom_fields_extends_entities', [ $this, 'custom_fields_extend_case' ] );

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

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Case', 'cf-civicrm' ),
			'description' => __( 'Add/Open CiviCRM Case (CiviCase) to contact', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/case/case_config.php',
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' =>  [ $this, 'processor' ],
			'magic_tags' => [ 'case_id' ],
		];

		return $processors;

	}

	/**
	 * Form pre processor callback.
	 *
	 * @since 1.0.3
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		/**
		 * Filter to abort case processing.
		 *
		 * To abort a case from being processed return true or,
		 * to abort the form from processing return an array like:
		 * [ 'note' => 'Some message', 'type' => 'success|error|info|warning|danger' ]
		 * The form processing will stop displaying 'Some message'
		 *
		 * @since 1.0.3
		 * @param bool|array $return Whether to abort the processing of a participant
		 * @param array $form_values The submitted form values
		 * @param array $config The processor config
		 * @param array $form The form config
		 * @param string $processid The process id
		 */
		$return = apply_filters( 'cfc_case_pre_processor_return', false, $form_values, $config, $form, $processid );

		if ( ! $return ) {
			// continue, processing happens in 'processor' callback
		} else {
			return $return;
		}

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.1
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		if ( isset( $config['dismiss_case'] ) ) {
			$existing_cases = civicrm_api3( 'Case', 'get', [
				'sequential' => 1,
				'contact_id' => $transient->contacts->{$this->contact_link},
				'case_type_id' => $config['case_type_id'],
				'is_deleted' => 0,
				'options' => [ 'limit' => 0 ],
			] );
		}

		$form_values['contact_id'] = $transient->contacts->{$this->contact_link}; // Contact ID set in Contact Processor
		$form_values['case_type_id'] = $config['case_type_id']; // Case Type ID
		$form_values['case_status_id'] = $config['case_status_id']; // Case Status ID

		if ( ! empty( $config['creator_id'] ) )
			$form_values['creator_id'] = strpos( $config['creator_id'], 'contact_' ) !== false ? $transient->contacts->{'cid_' . str_replace( 'contact_', '', $config['creator_id'] )} : $config['creator_id']; // Case Manager

		if( empty( $config['start_date'] ) )
			$form_values['start_date'] = date( 'YmdHis', strtotime( 'now' ) ); // Date format YYYYMMDDhhmmss

		if( ! empty( $config['dismiss_case'] ) && ! empty( $existing_cases['id'] ) ) {

			// return exisiting case id magic tag
			return [ 'case_id' => $existing_cases['id'] ];

		} else {
			try {
				$create_case = civicrm_api3( 'Case', 'create', $form_values );

				/**
				 * Broadcast case cretion
				 *
				 * @since 1.0.3
				 * @param array $result The api result
				 * @param array $params The api parameters
				 * @param array $config The processor config
				 * @param array $form The form config
				 */
				do_action( 'cfc_case_processor_case_create', $create_case, $form_values, $config, $form );

				return [ 'case_id' => $create_case['id'] ];

			} catch ( CiviCRM_API3_Exception $e ) {
				global $transdata;
				$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
				$transdata['error'] = true;
				$transdata['note'] = $error;
				return;
			}
		}

	}

	/**
	 * Add Case to extend custom fields autopopulation/presets.
	 *
	 * @since 1.0.3
	 * @param array $extends The entites array
	 * @return array $extends The filtered entities array
	 */
	public function custom_fields_extend_case( $extends ) {
		$extends[] = 'Case';
		return $extends;
	}
}
