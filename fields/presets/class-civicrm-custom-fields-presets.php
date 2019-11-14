<?php

/**
 * CiviCRM Caldera Forms Custom Fields Presets Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Custom_Fields_Presets {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	 * The custom fields data array.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $processors The custom fields data array
	 */
	public $custom_fields = [];

	/**
	 * Allowed CiviCRM field tyes.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $allowed_html_types Field types
	 */
	public $allowed_html_types = [ 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' ];

	/**
	 * The entites the custom fields extend.
	 *
	 * @since 1.0
	 * @access public
	 * @var array The entities
	 */
	public $extend_entities = [];

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

		// adds custom fields options Presets
		add_filter( 'caldera_forms_field_option_presets', [ $this, 'custom_fields_options_presets' ] );

		// auto-populate custom fields
		add_action( 'caldera_forms_autopopulate_types', [ $this, 'autopopulate_custom_fields_types' ] );
		add_filter( 'caldera_forms_render_get_field', [ $this, 'autopopulate_custom_fields_values' ], 20, 2 );

	}

	/**
	 * Adds custom fields options Presets.
	 *
	 * @uses 'caldera_forms_field_option_presets' filter
	 *
	 * @since 0.2
	 *
	 * @param array $presets The existing presets
	 * @return array $presets The modified presets
	 */
	public function custom_fields_options_presets( $presets ) {

		// get all custom fields
		$custom_fields = $this->custom_fields_get();

		if ( empty( $custom_fields ) ) return $presets;

		$extends = $this->entities_extend_get();

		array_map( function( $field ) use ( &$presets, $extends ) {

			if ( empty( $field['option_group_id'] ) ) return;

			if ( ! in_array( $field['custom_group_id.extends'], $extends ) ) return;

			if ( ! in_array( $field['html_type'], $this->allowed_html_types ) ) return;

			$custom_options = $this->option_values_get( $field['option_group_id'] );

			if ( ! $custom_options ) return;

			$presets['custom_' . $field['id']] = [
				'name' => sprintf( __( 'CiviCRM - %1$s - %2$s', 'cf-civicrm' ), $field['custom_group_id.title'], $field['label'] ),
				'data' => array_reduce( $custom_options, function( $options, $option ) {
					$options[] = $option['value'] . '|' . $option['label'];
					return $options;
				}, [] )
			];

		}, $custom_fields );

		return $presets;

	}

	/**
	 * Adds CiviCRM custom fields options that extend Activities and any contact
	 * type to CF Autopopulate field type.
	 *
	 * @uses 'caldera_forms_autopopulate_types' action
	 *
	 * @since 0.2
	 */
	public function autopopulate_custom_fields_types() {

		// get all custom fields
		$custom_fields = $this->custom_fields_get();

		if ( ! $custom_fields ) return;

		$extends = $this->entities_extend_get();

		array_map( function( $field ) use ( $extends ) {

			if ( empty( $field['option_group_id'] ) ) return;

			if ( ! in_array( $field['html_type'], $this->allowed_html_types ) ) return;

			if ( ! in_array( $field['custom_group_id.extends'], $extends ) ) return;

			echo "<option value=\"custom_{$field['id']}\"{{#is auto_type value=\"custom_{$field['id']}\"}} selected=\"selected\"{{/is}}>" . sprintf( __( 'CiviCRM - %1$s - %2$s', 'cf-civicrm' ), $field['custom_group_id.title'], $field['label'] ) . "</option>";

		}, $custom_fields );

	}

	/**
	 * Populates CiviCRM fields values for each CiviCRM CF Autopopulate custom field type.
	 *
	 * @uses 'caldera_forms_render_get_field' filter
	 *
	 * @since 0.2
	 *
	 * @param array $field The field to populate
	 * @param array $field The containing form
	 * @eturn array $field The populated field
	 */
	public function autopopulate_custom_fields_values( $field, $form ) {

		if ( ! isset( $field['config']['auto'] ) ) return $field;

		if ( strpos( $field['config']['auto_type'], 'custom_' ) === false ) return $field;

		// it's a custom field, get all custom fields
		$custom_fields = $this->custom_fields_get();

		if ( ! $custom_fields ) return $field;

		// custom field id
		$custom_field_id = (int) str_replace( 'custom_', '', $field['config']['auto_type'] );

		if ( empty( $custom_fields[$custom_field_id] ) ) return $field;

		// custom field settings
		$custom_field = $custom_fields[$custom_field_id];

		if ( empty( $custom_field['option_group_id'] ) ) return $field;

		if ( ! in_array( $custom_field['html_type'], $this->allowed_html_types ) ) return $field;

		if ( $field['config']['auto_type'] !== 'custom_' . $custom_field['id'] ) return $field;

		// get options
		$custom_options = $this->option_values_get( $custom_field['option_group_id'] );

		if ( ! $custom_options ) return $field;

		// populate field options
		$field['config']['option'] = array_reduce( $custom_options, function( $options, $option ) {
			$options[$option['value']] = [
				'value' => $option['value'],
				'label' => $option['label']
			];
			return $options;
		}, [] );

		return $field;

	}

	/**
	 * Retrieves custom field data from CiviCRM.
	 *
	 * @since 0.2
	 *
	 * @return array|bool $custom_fields The custom fields, or false
	 */
	public function custom_fields_get() {

		// return data if it's already retrieved
		if ( ! empty( $this->custom_fields ) ) return $this->custom_fields;

		// get all custom fields
		$custom_fields = civicrm_api3( 'CustomField', 'get', [
			'is_active' => 1,
			'return' => [ 'name', 'label', 'custom_group_id', 'option_group_id', 'html_type', 'custom_group_id.extends', 'custom_group_id.title' ],
			'options' => [ 'limit' => 0 ],
		] );


		if ( ! is_array( $custom_fields ) && ! $custom_fields['count'] ) return false;

		// get option values
		// $option_group_ids = array_column( $custom_fields['values'], 'option_group_id' );

		$this->custom_fields = $custom_fields['values'];

		return $this->custom_fields;

	}

	/**
	 * Get option values for a given option group.
	 *
	 * @since 1.0
	 * @param int $option_group_id The option group id
	 * @return array|false $option_values The option values, or false
	 */
	public function option_values_get( $option_group_id ) {

		try {

			$option_values = civicrm_api3( 'OptionValue', 'get', [
				'sequential' => 1,
				'option_group_id' => $option_group_id,
				'options' => ['limit' => 0, 'sort' => 'weight ASC'],
			] );

		} catch ( CiviCRM_API3_Exception $e ) {

			// return false if there's an error
			return false;

		}

		if ( $option_values['count'] && ! $option_values['is_error'] ) return $option_values['values'];

		return false;

	}

	/**
	 * Get extend entities to return custom fields for autopopulation and presets.
	 *
	 * @since 1.0
	 * @return array $extends The entites
	 */
	public function entities_extend_get() {

		$extends = array_merge( [ 'Contact', 'Activity' ], CRM_Contact_BAO_ContactType::basicTypes(), CRM_Contact_BAO_ContactType::subTypes() );

		return apply_filters( 'cfc_custom_fields_extends_entities', $extends );
	}

}

