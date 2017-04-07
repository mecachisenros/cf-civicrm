<?php

/**
 * CiviCRM Caldera Forms Im Processor Class.
 *
 * @since 0.3
 */
class CiviCRM_Caldera_Forms_Im_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.3
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_im';

	/**
	 * Initialises this object.
	 *
	 * @since 0.3
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );
		// filter form before rendering
		add_filter( 'caldera_forms_render_get_form', array( $this, 'pre_render') );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.3
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Im (Instant Messenger)', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM Im to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/im/im_config.php',
			'processor' => array( $this, 'processor' ),
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.3
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$im = civicrm_api3( 'Im', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['location_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			$form_values = array();
			foreach( $config as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values[$key] = $mapped_field;
				}
			}

			if( ! empty( $form_values ) ){
				$form_values['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass Im ID if we got one
				if ( isset( $im ) && is_array( $im ) ) {
					$form_values['id'] = $im['id']; // Im ID
				} else {
	                $form_values['location_type_id'] = $config['location_type_id']; // Im Location type set in Processor config
	            }

				$create_im = civicrm_api3( 'Im', 'create', $form_values );
			}
		}
	}

	/**
	 * Autopopulates Form with Civi data
	 *
	 * @uses 'caldera_forms_render_get_form' filter
	 *
	 * @since 0.3
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function pre_render( $form ){

		// globalised transient object
		global $transdata;

		foreach ( $form['processors'] as $processor => $pr_id ) {
			if( $pr_id['type'] == $this->key_name ){

				if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
					try {

						$civi_contact_im = civicrm_api3( 'Im', 'getsingle', array(
							'sequential' => 1,
							'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
							'location_type_id' => $pr_id['config']['location_type_id'],
						));

					} catch ( Exception $e ) {
						// Ignore if we have more than one Im with same location type or none
					}
				}

				unset( $pr_id['config']['contact_link'], $pr_id['config']['location_type_id'] );

				if ( isset( $civi_contact_im ) && ! isset( $civi_contact_im['count'] ) ) {
					foreach ( $pr_id['config'] as $field => $value ) {
						if ( ! empty( $value ) ) {
							$form['fields'][$value]['config']['default'] = $civi_contact_im[$field];
						}
					}
				}

				// Clear Im data
				unset( $civi_contact_im );
			}
		}

		return $form;
	}
}
