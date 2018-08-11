<?php

/**
 * CiviCRM Caldera Forms State Field Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Field_State {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

    /**
     * Field key name
     *
     * @since 0.4.4
     * @var string $key_name The field key name
     */
    public $key_name = 'civicrm_state';

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
	 * @since 0.2
	 *
	 * @param array $field_types The existing fields configuration
	 * @return array $field_types The modified fields configuration
	 */
	public function register_field_type( $field_types ) {

		$field_types[$this->key_name] = [
			'field' => __( 'CiviCRM State/Province', 'caldera-forms-civicrm' ),
			'file' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/field.php',
			'category' => __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'description' => __( 'CiviCRM State/Province dropdown', 'caldera-forms-civicrm' ),
			'setup' => [
				'template' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/config.php',
				'preview' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/preview.php',
				'default' => [
					'placeholder' => __( 'Select a State/Province', 'caldera-forms-civicrm' ),
					'default' => $this->plugin->helper->get_civicrm_settings( 'defaultContactStateProvince' )
				],
			],
		];

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

		$states = $this->plugin->helper->get_state_province();

		// set as view if we get a match
		foreach( $states as $state_id => $state ) {
			if ( $state_id == $field_value ) {
				$field_value = esc_html( $state['name'] );
				break;
			}
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

		if ( $field['type'] == 'civicrm_state' ){
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
