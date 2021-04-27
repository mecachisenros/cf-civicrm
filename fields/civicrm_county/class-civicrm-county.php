<?php

/**
 * CiviCRM Caldera Forms County Field Class.
 *
 * @since 1.1
 */
class CiviCRM_Caldera_Forms_Field_County {

	/**
	 * Plugin reference.
	 *
	 * @since 1.1
	 */
	public $plugin;

	/**
	 * Field key name
	 *
	 * @since 1.1
	 * @var string $key_name The field key name
	 */
	public $key_name = 'civicrm_county';

	/**
	 * Initialises this object.
	 *
	 * @since 1.1
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register Caldera Forms callbacks
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 1.1
	 */
	public function register_hooks() {

		// add custom fields to Caldera UI
		add_filter( 'caldera_forms_get_field_types', [ $this, 'register_field_type' ] );
		// enqueue scripts
		add_filter( 'caldera_forms_render_get_form', [ $this, 'enqueue_scripts' ], 10 );

		// render state name
		add_filter( 'caldera_forms_view_field_civicrm_state', [ $this, 'field_render_view' ], 10, 3 );
		// render state name in email summary
		add_filter( 'caldera_forms_magic_summary_field_value', [ $this, 'field_render_summary' ], 10, 3 );

	}

	/**
	 * Adds the field definition for this field type to Caldera UI.
	 *
	 * @uses 'caldera_forms_get_field_types' filter
	 *
	 * @since 1.1
	 *
	 * @param array $field_types The existing fields configuration
	 * @return array $field_types The modified fields configuration
	 */
	public function register_field_type( $field_types ) {

		$field_types[$this->key_name] = [
			'field' => __( 'CiviCRM County', 'cf-civicrm' ),
			'file' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_county/field.php',
			'category' => __( 'CiviCRM', 'cf-civicrm' ),
			'description' => __( 'CiviCRM County', 'cf-civicrm' ),
			'setup' => [
				'template' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_county/config.php',
				'preview' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_county/preview.php',
				'default' => [
					'placeholder' => __( 'Select a County', 'cf-civicrm' ),
					// 'default' => $this->plugin->helper->get_civicrm_settings( 'defaultContactStateProvince' )
				],
			],
		];

		return $field_types;

	}

	/**
	 * Renders the view for this field type in the Caldera UI.
	 *
	 * @since 1.1
	 *
	 * @param array $field_value The field value to populate
	 * @param array $form The containing form
	 * @return array $field_value The modified field value
	 */
	public function field_render_view( $field_value, $field, $form ) {

		$counties = $this->plugin->helper->get_counties();

		// set as view if we get a match
		foreach( $counties as $county_id => $county ) {
			if ( $county_id == $field_value ) {
				$field_value = esc_html( $county['name'] );
				break;
			}
		}

		return $field_value;

	}

	/**
	 * Renders the label for this field type in the email summary.
	 *
	 * @since 1.1
	 *
	 * @param  string $field_value The field value
	 * @param  array $field The field config
	 * @param  array $form The form config
	 * @return string $field_value The modified field value
	 */
	public function field_render_summary( $field_value, $field, $form ){

		if ( $field['type'] == $this->key_name ){
			$field_value = $this->field_render_view( $field_value, $field, $form );
		}

		return $field_value;
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 0.4.4
	 *
	 * @param array $form Form config
	 * @return array $form Form config
	 */
	public function enqueue_scripts( $form ) {

		foreach ( $form['fields'] as $field_id => $field ) {
			if ( $field['type'] == $this->key_name ) {
				wp_enqueue_style( 'cfc-select2' );
				wp_enqueue_script( 'cfc-select2' );
				break;
			}
		}

		return $form;
	}

}
