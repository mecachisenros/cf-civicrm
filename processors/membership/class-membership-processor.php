<?php

/**
 * CiviCRM Caldera Forms Membership Processor Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Membership_Processor {

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
	 * Current contact's memeberships.
	 *
	 * @since 0.4.4
	 * @var array
	 */
	protected $has_memberships;

	/**
	 * Membership statuses considered 'current'.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array The membership statuses considered 'current'
	 */
	public $membership_statuses_current;

	/**
	 * The processor key.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_membership';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// filter form before rendering
		add_filter( 'caldera_forms_render_get_form', [ $this, 'pre_render' ] );
	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.4
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = [
			'name' =>  __( 'CiviCRM Membership', 'cf-civicrm' ),
			'description' =>  __( 'Create/Renew CiviCRM Memberhips.', 'cf-civicrm' ),
			'author' =>  'Andrei Mondoc',
			'template' =>  CF_CIVICRM_INTEGRATION_PATH . 'processors/membership/membership_config.php',
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'magic_tags' => [ 'processor_id' ],
		];

		return $processors;

	}

	/**
	 * Form pre-processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

		global $transdata;

		/**
		 * Filter processor config.
		 *
		 * @since 1.0.3
		 * @param array $config The processor config
		 * @param array $form The form config
		 * @param string $processid The process id
		 */
		$config = apply_filters( 'cfc_membership_pre_processor_config', $config, $form, $processid );

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		// price field value, if applicable
		$price_field_value = isset( $config['is_price_field_based'] ) ?
			$this->plugin->helper->get_price_field_value( $form_values['price_field_value'] ) : false;

		$form_values['membership_type_id'] = $price_field_value ? $price_field_value['membership_type_id'] : $config['membership_type_id'];

		// is member?
		try {
			$is_member = civicrm_api3( 'Membership', 'getsingle', [
				'contact_id' => $transient->contacts->{$this->contact_link},
				'membership_type_id' => $form_values['membership_type_id'],
			] );
		} catch ( CiviCRM_API3_Exception $e ) {
			$is_member = false;
		}

		/**
		 * Filter to abort membership processing.
		 *
		 * To abort a membership from being processed return true or,
		 * to abort the form from processing return an array like:
		 * [ 'note' => 'Some message', 'type' => 'success|error|info|warning|danger' ]
		 * The form processing will stop displaying 'Some message'
		 *
		 * @since 1.0.3
		 * @param bool|array $return Whether to abort the processing of a memebership
		 * @param bool|array $membership The current membership being processed or false
		 * @param array $form_values The submitted form values for this membership
		 * @param array $config The processor config
		 * @param array $form The form config
		 * @param string $processid The process id
		 */
		$return = apply_filters( 'cfc_membership_pre_processor_return', false, $is_member, $form_values, $config, $form, $processid );

		if ( ! empty( $transient->contacts->{$this->contact_link} ) && ! $return ) {

			$form_values['contact_id'] = $transient->contacts->{$this->contact_link};

			if ( ! empty( $config['campaign_id'] ) ) $form_values['campaign_id'] = $config['campaign_id'];

			// renew/extend necessary params
			if ( isset( $config['is_renewal'] ) && isset( $is_member['id'] ) ) {
				$form_values['id'] = $is_member['id'];

				// Ask the API to calculate the status for us.
				$form_values['skipStatusCal'] = 0;
			}

			$form_values['source'] = isset( $form_values['source'] ) ? $form_values['source'] : $form['name'];

			// set start and join date if is not renewal
			if ( ! $config['is_renewal'] ) {
				$form_values['join_date'] = ! empty( $form_values['join_date'] ) ? date( 'Ymd', strtotime( $form_values['join_date'] ) ) : date('Ymd');
				$form_values['start_date'] = ! empty( $form_values['start_date'] ) ? date( 'Ymd', strtotime( $form_values['start_date'] ) ) : date('Ymd');
			} else {
				//remove join, start, and end dates otherwise
				unset( $form_values['join_date'], $form_values['start_date'], $form_values['end_date'] );
			}

			// get num_terms
			$form_values['num_terms'] = $this->get_num_terms( $form_values, $price_field_value );

			/**
			 * Filter form values/membership params before processing or storing in transient.
			 *
			 * @since 1.0.3
			 * @param array $form_values The submitted form value/membership api params
			 * @param array $config The processor config
			 * @param array $form The form config
			 * @param string $processid The process id
			 */
			$form_values = apply_filters( 'cfc_membership_pre_processor_form_values', $form_values, $config, $form, $processid );

			$transient->memberships->{$config['processor_id']}->params = $form_values;

			$this->plugin->transient->save( $transient->ID, $transient );

			// free memberships
			if( ! $config['is_monetary'] ) {
				unset( $form_values['is_price_field_based'] );
				try {
					$create_member = civicrm_api3( 'Membership', 'create', $form_values );
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		} elseif ( isset( $return['type'] ) && isset( $return['note'] ) ) {
			return $return;
		}

        Caldera_Forms::set_submission_meta( 'processor_id', $config['processor_id'], $form, $config['processor_id'] );
	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id (it may not be set)
	 */
	public function processor( $config, $form, $processid ) {
		return ['processor_id' => $config['processor_id'] ];
	}

	/**
	 * Autopopulates Form with Civi data.
	 *
	 * @uses 'caldera_forms_render_get_form' filter
	 *
	 * @since 0.4.4
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function pre_render( $form ) {
		// continue as normal if form has no processors
		if( empty( $form['processors'] ) ) return $form;
		// enqueue forntend script
		wp_enqueue_script( 'cfc-front' );

		// cfc transient object
		$transient = $this->plugin->transient->get();
		// get membership current statuses
		$membership_statuses = $this->get_membership_statuses_current();

		foreach ( $form['processors'] as $processor => $pr_id ) {
			if( $pr_id['type'] == $this->key_name && isset( $pr_id['runtimes'] ) ){

				$contact_link = $pr_id['contact_link'] = 'cid_'.$pr_id['config']['contact_link'];

				if ( isset( $transient->contacts->{$contact_link} ) ) {

					try {

						$is_member = civicrm_api3( 'Membership', 'get', [
							'sequential' => 1,
							'is_test' => 0,
							'status_id' => [ 'IN' => $membership_statuses ? $membership_statuses : [ 'New', 'Current', 'Grace' ] ],
							'contact_id' => $transient->contacts->{$contact_link},
							'options' => [ 'limit' => 0 ]
						] );

					} catch ( CiviCRM_API3_Exception $e ) {
						// Ignore
					}
				}

				if ( isset( $is_member ) && ! $is_member['is_error'] )
					$this->has_memberships = $is_member['values'];

				// if ( isset( $is_member ) && ! isset( $is_member['count'] ) ) {
				// 	$form = $this->plugin->helper->map_fields_to_prerender(
				// 		$pr_id['config'],
				// 		$form,
				// 		$this->fields_to_ignore,
				// 		$is_member
				// 	);
				// }

				// Clear member data
				unset( $is_member );
			}
		}

		return $form;
	}

	/**
	 * Get Membership number of terms.
	 *
	 * @since 0.4.4
	 *
	 * @param array $form_values The submitted form values/data
	 * @param array|boolean $price_field_value The price field value or false
	 * @return int $num_terms The membership number of terms
	 */
	public function get_num_terms( $form_values, $price_field_value = false ) {
		// processor num_terms
		if ( $form_values && ! empty( $form_values['num_terms'] ) )
			return $form_values['num_terms'];

		// fallback to price field/price set num_term if applicable
		if ( $price_field_value && ! empty( $price_field_value['membership_num_terms'] ) )
			return $price_field_value['membership_num_terms'];

		// fallback to at least 1 term, this will be the term setup in the Membership Type settings
		return 1;
	}

	/**
	 * Get membership statuses considered 'current'.
	 *
	 * @since 0.4.4
	 * @return array $membership_statuses_current The membership statuses
	 */
	public function get_membership_statuses_current() {

		if ( ! empty( $this->membership_statuses_current ) ) return $this->membership_statuses_current;

		try {
			$statuses = civicrm_api3( 'MembershipStatus', 'get', [
				'sequential' => 1,
				'return' => [ 'name' ],
				'is_current_member' => 1,
				'options' => [ 'limit' => 0 ]
			] );
		} catch ( CiviCRM_API3_Exception  $e ) {

		}

		if ( is_array( $statuses ) && $statuses['count'] && ! $statuses['is_error'] )
			$this->membership_statuses_current = array_column( $statuses['values'], 'name' );

		return $this->membership_statuses_current;
	}
}
