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

		$field_types['civicrm_contact_reference'] = [
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

		if ( $civi_field == 'current_employer' && $field['type'] == 'civicrm_contact_reference' ) {
			if ( ! is_numeric( $mapped_field ) && isset( $field['config']['new_organization'] ) ) {
				$employer = civicrm_api3( 'Contact', 'create', [
					'contact_type' => 'Organization',
					'organization_name' => $mapped_field,
				] );	
			} else {
				$employer = civicrm_api3( 'Contact', 'get', [
					'contact_id' => $mapped_field,
					'return' => 'organization_name'
				] );
			}
			return $employer['values'][$employer['id']]['organization_name'];
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
		if ( $civi_field == 'current_employer' && $field['type'] == 'civicrm_contact_reference' ) {
			$employer = civicrm_api3( 'Contact', 'get', [ 'contact_type' => 'Organization', 'organization_name' => $entity[$civi_field] ] );
			return $employer['id'];
		}
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
		wp_register_script( 'cfc-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/js/select2.js', [ 'jquery' ], CF_CIVICRM_INTEGRATION_VER );
		
		$reference = false;
		
		foreach ( $form['fields'] as $field_id => $field ) {
			if ( $field['type'] == 'civicrm_contact_reference' ) {
				$reference = true;
			}
		}

		if( $reference ){
			wp_enqueue_script( 'cfc-select2' );
			wp_enqueue_style( 'cfc-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/css/select2.min.css', [], CF_CIVICRM_INTEGRATION_VER );
			wp_localize_script( 'cfc-select2', 'cfc', [ 'url' => admin_url( 'admin-ajax.php' ) ] );
		}
		return $form;
	}

}
