<?php

/**
 * CiviCRM Caldera Forms Premium Field Class.
 *
 * @since 1.0
 */
class CiviCRM_Caldera_Forms_Field_Premium {

	/**
	 * Plugin reference.
	 *
	 * @since 1.0
	 */
	public $plugin;

	/**
	 * Field key name.
	 *
	 * @since 1.0
	 * @var string Field key name
	 */
	public $key_name = 'civicrm_premium';

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register Caldera Forms callbacks
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

		// add custom fields to Caldera UI
		add_filter( 'caldera_forms_get_field_types', [ $this, 'register_field_type' ] );
		// add classes
		add_filter( 'caldera_forms_render_field_classes_type-' . $this->key_name, [ $this, 'add_classes' ], 10, 3 );

		add_filter( 'caldera_forms_render_get_field', [ $this, 'filter_field_config' ], 10, 2 );

	}

	/**
	 * Adds the field definition for this field type to Caldera UI.
	 *
	 * @uses 'caldera_forms_get_field_types' filter
	 *
	 * @since 1.0
	 *
	 * @param array $field_types The existing fields configuration
	 * @return array $field_types The modified fields configuration
	 */
	public function register_field_type( $field_types ) {

		$field_types[$this->key_name] = [
			'field' => __( 'CiviCRM Premium', 'cf-civicrm' ),
			'file' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_premium/field.php',
			'category' => __( 'CiviCRM', 'cf-civicrm' ),
			'description' => __( 'CiviCRM Premiums for Order processors', 'cf-civicrm' ),
			'setup' => [
				'template' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_premium/config.php',
				'preview' => CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_premium/preview.php',
				'default' => [
					'active_class' => 'btn-success',
					'default_class' => 'btn-default',
					'no_thanks' => 'No thank you'
				]
			],
			// borough styles form CF's toggle_switch field
			'styles' => [
				CFCORE_URL . 'fields/toggle_switch/css/setup.css',
				CFCORE_URL . 'fields/toggle_switch/css/toggle.css'
			],
			'scripts' => [
				CF_CIVICRM_INTEGRATION_URL . 'fields/civicrm_premium/js/premium.js'
			]
		];

		return $field_types;

	}

	/**
	 * Add cf-toggle-switch class.
	 *
	 * @since 1.0
	 * @param array $field_classes The classes array
	 * @param array $field The field config
	 * @param array $form The form config
	 */
	public function add_classes( $field_classes, $field, $form ) {
		$field_classes['control_wrapper'][] = 'cf-toggle-switch';
		return $field_classes;
	}

	/**
	 * Filter this field type and adds premium/product data to it's config.
	 *
	 * @since 1.0
	 * @param array $field The field config
	 * @param array $form The form config
	 * @return array $field The filtered field
	 */
	public function filter_field_config( $field, $form ) {

		if ( $field['type'] != $this->key_name ) return $field;

		if ( isset( $field['config']['premium_id'] ) )
			$premium = civicrm_api3( 'Product', 'getsingle', [ 'id' => $field['config']['premium_id'] ] );

		if ( ! $premium ) return $field;

		$premium_config = [
			'name' => $premium['name'],
			'desc' => $premium['description'],
			'image' => $premium['image'] ? $premium['image'] : false,
			'thumbnail' => $premium['thumbnail'] ? $premium['thumbnail'] : false,
			'min' => sprintf( __( 'Minimum: %s', 'cf-civicrm' ), $premium['min_contribution'] ),
			'min_clean' => $premium['min_contribution'],
			'options' => $premium['options'] ? $this->to_array( $premium['options'] ) : false
		];

		$field['config'] = array_merge( $field['config'], $premium_config );

		return $field;
	}

	/**
	 * Format product/premium options.
	 *
	 * @since 1.0
	 * @param string $options Comma separated values
	 * @return array $options The options array
	 */
	public function to_array( string $options ) {
		$options = preg_replace( '/\s+/', '', $options );
		return explode( ',', $options );
	}

}
