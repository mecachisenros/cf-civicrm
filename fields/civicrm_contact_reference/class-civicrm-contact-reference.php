<?php

/**
 * CiviCRM Crraldera Forms Contact Reference Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Contact_Reference {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	 * Field key name.
	 *
	 * @since 0.4.4
	 * @var string $key_name Field key name
	 */
	public $key_name = 'civicrm_contact_reference';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.4.4
	 */
	public function register_hooks() {

		// add custom fields to Caldera UI
		add_filter( 'caldera_forms_get_field_types', [ $this, 'register_field_type' ] );
		add_filter( 'caldera_forms_render_get_form', [ $this, 'enqueue_scripts' ], 10 );
		// handle current_employer field
		add_filter( 'cfc_filter_mapped_field_to_processor', [ $this, 'handle_current_employer_field' ], 10, 5 );
		add_filter( 'cfc_filter_mapped_field_to_prerender', [ $this, 'pre_render_current_employer_value' ], 10, 5 );

		// render country name
		add_filter( 'caldera_forms_view_field_' . $this->key_name, [ $this, 'field_render_view' ], 10, 3 );
		// render country name in email summary
		add_filter( 'caldera_forms_magic_summary_field_value', [ $this, 'field_render_summary' ], 10, 3 );
	}

	/**
	 * Adds the field definition for this field type to Caldera UI.
	 *
	 * @uses 'caldera_forms_get_field_types' filter
	 *
	 * @since 0.4.4
	 *
	 * @param array $field_types The existing fields configuration
	 * @return array $field_types The modified fields configuration
	 */
	public function register_field_type( $field_types ) {

		$field_types[$this->key_name] = [
			'field' => __( 'CiviCRM Contact Reference', 'caldera-forms-civicrm' ),
			'file' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_contact_reference/field.php',
			'category' => __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'description' => __( 'CiviCRM Contact Reference field', 'caldera-forms-civicrm' ),
			'setup' => [
				'template' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_contact_reference/config.php',
				'preview' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_contact_reference/preview.php',
				'default' => [
					'placeholder' => __( 'Search Contacts', 'caldera-forms-civicrm' )
				],
			],
			// 'scripts' => [
			// 	CF_CIVICRM_INTEGRATION_URL . 'assets/js/select2.js',
			// ],
			// 'styles' => [
			// 	CF_CIVICRM_INTEGRATION_URL . 'assets/css/select2.min.css',
			// ],
		];

		return $field_types;

	}

	/**
	 * Filter current_employer mapped field value.
	 *
	 * @since  0.4.4
	 *
	 * @param string|int $mapped_field The mapped value
	 * @param string $civi_field The field for an entity i.e. 'contact_id', 'current_employer', etc.
	 * @param array $field The field config
	 * @param array $config processor config
	 * @param array $form Form config
	 */
	public function handle_current_employer_field( $mapped_field, $civi_field, $field, $config, $form ) {

		if ( $field['type'] != 'civicrm_contact_reference' ) return $mapped_field;

		switch ( $civi_field ) {
			case 'current_employer':
				$org = $this->get_organisation( $mapped_field, $field );
				if ( $org ) return $org;
				return '';
				break;
			case 'organization_name':
				$org = $this->get_organisation( $mapped_field, $field );
				if ( $org ) return $org['organization_name'];
				return '';
				break;
		}

		return $mapped_field;

	}

	/**
	 * Prerenderd default current_employer.
	 *
	 * @since  0.4.4
	 *
	 * @param string|int $value The default value
	 * @param string $civi_field The field for an entity i.e. 'contact_id', 'current_employer', etc.
	 * @param array $field The field config
	 * @param array $entity The current entity, i.e. Contact, Address, etc
	 * @param array $config processor config
	 */
	public function pre_render_current_employer_value( $value, $civi_field, $field, $entity, $config ) {

		if ( $field['type'] != 'civicrm_contact_reference' ) return $value;

		if ( $civi_field != 'current_employer' ) return $value;

		$employer = civicrm_api3( 'Contact', 'get', [
			'contact_type' => 'Organization',
			'organization_name' => $entity[$civi_field],
			'return' => 'organization_name',
			'options' => [ 'limit' => 1 ]
		] );

		if ( isset( $employer['count'] ) && $employer['count'] ) return $employer['id'];

		return $value;
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
		$reference = false;

		foreach ( $form['fields'] as $field_id => $field ) {
			if ( $field['type'] == 'civicrm_contact_reference' ) {
				$reference = true;
			}
		}

		if( $reference ){
			wp_enqueue_style( 'cfc-select2' );
			wp_enqueue_script( 'cfc-select2' );
			wp_localize_script( 'cfc-select2', 'cfc', [ 'url' => admin_url( 'admin-ajax.php' ) ] );
		}
		return $form;
	}

	/**
	 * Renders the view for this field type in the Caldera UI.
	 *
	 * @since 0.4.4
	 *
	 * @param array $field_value The field value to populate
	 * @param array $form The containing form
	 * @return array $field_value The modified field value
	 */
	public function field_render_view( $field_value, $field, $form ) {

		// use API to retrieve contact sort name
		$contact_data = civicrm_api3( 'Contact', 'get', [
			'id' => $field_value,
			'return' => ['sort_name'],
		] );

		// set as view if we get one
		if ( $contact_data['is_error'] == '0' ) {
			$item = array_pop( $contact_data['values'] );
			$field_value = esc_html( $item['sort_name'] );
		}

		return $field_value;

	}

	/**
	 * Renders the label for this field type in the email summary.
	 *
	 * @since 0.4.4
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
	 * Gets or creates (if it does not exist) an organisations.
	 *
	 * @since 1.0.3
	 * @param int|strin $value Contact id o r organisation name
	 * @param array $field The field config
	 * @return array|bool The organisation name and contact id or false
	 */
	public function get_organisation( $value, $field ) {

		if ( ! is_numeric( $value ) && isset( $field['config']['new_organization'] ) ) {
			$employer = civicrm_api3( 'Contact', 'create', [
				'contact_type' => 'Organization',
				'organization_name' => $value,
			] );
		} else {
			$employer = civicrm_api3( 'Contact', 'get', [
				'contact_id' => $value,
				'return' => 'organization_name'
			] );
		}

		if ( isset( $employer['count'] ) && $employer['count'] )
			return [
				'organization_name' => $employer['values'][$employer['id']]['organization_name'],
				'employer_id' => $employer['id']
			];

		return false;

	}

}
