<?php

/**
 * CiviCRM Caldera Forms Address Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Address_Processor {

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
	public $key_name = 'civicrm_address';

	public $fields = [ 'name', 'is_primary', 'is_billing', 'street_address', 'supplemental_address_1', 'supplemental_address_2', 'city', 'state_province_id', 'postal_code', 'county_id', 'country_id', 'geo_code_1', 'geo_code_2' ];

	/**
	 * Fields to ignore while prepopulating
	 *
	 * @since 0.4
	 * @access public
	 * @var array $fields_to_ignore Fields to ignore
	 */
	public $fields_to_ignore = [ 'contact_link', 'location_type_id' ];

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// filter form before rendering
		add_filter( 'caldera_forms_render_get_form', [ $this, 'pre_render' ] );
		// address custom fields
		add_filter( 'cfc_custom_fields_extends_entities', [ $this, 'custom_fields_extend_address' ] );
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
			'name' => __( 'CiviCRM Address', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM address to contacts', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/address/address_config.php',
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
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		if ( ! empty( $transient->contacts->{$this->contact_link} ) ) {

			try {
				$address = civicrm_api3( 'Address', 'getsingle', [
					'contact_id' => $transient->contacts->{$this->contact_link},
					'location_type_id' => $config['location_type_id'],
				] );
			} catch ( CiviCRM_API3_Exception $e ) {
				// Ignore if none found
			}

			// Get form values
			$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

			if( ! empty( $form_values ) ) {
				$form_values['contact_id'] = $transient->contacts->{$this->contact_link}; // Contact ID set in Contact Processor

				// Pass address ID if we got one
				if ( isset( $address ) && is_array( $address ) ) {
					$form_values['id'] = $address['id']; // Address ID
				}

				// Always add Location Type ID
				$form_values['location_type_id'] = $config['location_type_id'];

				// FIXME
				// Concatenate DATE + TIME
				// $form_values['activity_date_time'] = $form_values['activity_date_time'];

				if ( isset( $config['is_override'] ) ) {
					foreach ( $this->fields as $key => $field ) {
						if ( ! isset( $form_values[$field] ) )
							$form_values[$field] = '';
					}
				}

				try {
					$create_address = civicrm_api3( 'Address', 'create', $form_values );
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		}
	}

	/**
	 * Autopopulates Form with Civi data
	 *
	 * @uses 'caldera_forms_render_get_form' filter
	 *
	 * @since 0.2
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function pre_render( $form ){
		// continue as normal if form has no processors
		if( empty( $form['processors'] ) ) return $form;

		// cfc transient object
		$transient = $this->plugin->transient->get();

		foreach ( $form['processors'] as $processor => $pr_id ) {

			if( $pr_id['type'] == $this->key_name && isset( $pr_id['runtimes'] ) ){

				$contact_link = 'cid_'.$pr_id['config']['contact_link'];

				if ( isset( $transient->contacts->{$contact_link} ) ) {
					try {

						$address = civicrm_api3( 'Address', 'getsingle', [
							'contact_id' => $transient->contacts->{$contact_link},
							'location_type_id' => $pr_id['config']['location_type_id'],
						] );

					} catch ( CiviCRM_API3_Exception $e ) {
						// Ignore if we have more than one address with same location type
					}
				}

				if ( isset( $address ) && ! isset( $address['count'] ) ) {
					$form = $this->plugin->helper->map_fields_to_prerender(
						$pr_id['config'],
						$form,
						$this->fields_to_ignore,
						$address
					);
				}

				// Clear Address data
				unset( $address );
			}
		}

		return $form;
	}

	/**
	 * Add Address to extend custom fields autopopulation/presets.
	 *
	 * @since 1.0
	 * @param array $extends The entites array
	 * @return array $extends The filtered entities array
	 */
	public function custom_fields_extend_address( $extends ) {
	  $extends[] = 'Address';
	  return $extends;
	}
}
