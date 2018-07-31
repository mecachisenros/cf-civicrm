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
