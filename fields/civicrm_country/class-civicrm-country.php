<?php

/**
 * CiviCRM Caldera Forms Country Field Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Field_Country {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register Caldera Forms callbacks
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 0.2
	 */
	public function register_hooks() {

		// add custom fields to Caldera UI
		add_filter( 'caldera_forms_get_field_types', array( $this, 'register_field_type' ) );

		// render country name
		add_filter( 'caldera_forms_view_field_civicrm_country', array( $this, 'field_render_view' ), 10, 3 );
		// render country name in email summary
		add_filter( 'caldera_forms_magic_summary_field_value', array( $this, 'field_render_summary' ), 10, 3 );

	}

	/**
	 * Adds the field definition for this field type to Caldera UI.
	 *
	 * @uses 'caldera_forms_get_field_types' filter
	 *
	 * @since 0.2
	 *
	 * @param array $field_types The existing fields configuration
	 * @return array $field_types The modified fields configuration
	 */
	public function register_field_type( $field_types ) {

		$field_types['civicrm_country'] = array(
			'field' => __( 'CiviCRM Country', 'caldera-forms-civicrm' ),
			'file' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/field.php',
			'category' => __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'description' => __( 'CiviCRM Country dropdown', 'caldera-forms-civicrm' ),
			'setup' => array(
				'template' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/config.php',
				'preview' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/preview.php',
				'default' => array(
					'placeholder' => __( 'Select a Country', 'caldera-forms-civicrm' ),
					'default' => $this->plugin->helper->get_civicrm_settings( 'defaultContactCountry' )
				),
			),
		);

		return $field_types;

	}

	/**
	 * Renders the view for this field type in the Caldera UI.
	 *
	 * @since 0.2
	 *
	 * @param array $field_value The field value to populate
	 * @param array $form The containing form
	 * @return array $field_value The modified field value
	 */
	public function field_render_view( $field_value, $field, $form ) {

		// use API to retrieve Country name
		$country_data = civicrm_api3( 'Country', 'get', array(
			'id' => $field_value,
		));

		// set as view if we get one
		if ( $country_data['is_error'] == '0' ) {
			$item = array_pop( $country_data['values'] );
			$field_value = esc_html( $item['name'] );
		}

		return $field_value;

	}

	/**
	 * Renders the label for this field type in the email summary.
	 *
	 * @since 0.4.1
	 *
	 * @param  string $field_value The field value
	 * @param  array $field The field config
	 * @param  array $form The form config
	 * @return string $field_value The modified field value
	 */
	public function field_render_summary( $field_value, $field, $form ){

		if ( $field['type'] == 'civicrm_country' ){
			$field_value = $this->field_render_view( $field_value, $field, $form );
		}

		return $field_value;
	}

}
