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
	public $custom_fields_data = [];

	/**
	 * Allowed CiviCRM field tyes.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $allowed_html_types Field types
	 */
	public $allowed_html_types = [ 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' ];

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
		$customFields = $this->custom_fields_data_get();

		$custom = [];
		if ( $customFields && ! $customFields['is_error'] && $customFields['count'] != 0 ) {
			foreach ( $customFields['values'] as $key => $field ) {
				if ( in_array( $field['html_type'], $this->allowed_html_types ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
					// get custom group
					$params['id'] = $field['custom_group_id'];
					$customGroup = [];
					CRM_Core_BAO_CustomGroup::retrieve( $params, $customGroup );

					// get options
					$customOptions = CRM_Core_OptionGroup::valuesByID( (int)$field['option_group_id'] );

					// contact types and activity for filtering
					$extends = array_merge( [ 'Contact', 'Activity' ], CRM_Contact_BAO_ContactType::basicTypes(), CRM_Contact_BAO_ContactType::subTypes() );

					if ( in_array( $customGroup['extends'], $extends ) ) {
						$options = [];
						foreach ( $customOptions as $key => $value ) {
							$options[] = $key.'|'.$value;
						}
						$custom[$field['name']] = [
							'name' => sprintf( __( 'CiviCRM - %1$s - %2$s', 'caldera-forms-civicrm' ), $customGroup['title'], $field['label'] ),
							'data' => $options,
						];
					}
				}
			}
		}
	
		$presets = array_merge( $custom, $presets );
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
		$customFields = $this->custom_fields_data_get();

		if ( $customFields && !$customFields['is_error'] && $customFields['count'] != 0 ) {

			$custom = [];
			foreach ( $customFields['values'] as $key => $field ) {
				if ( in_array( $field['html_type'], $thia->allowed_html_types ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
					// get custom group
					$params['id'] = $field['custom_group_id'];
					$customGroup = [];
					CRM_Core_BAO_CustomGroup::retrieve( $params, $customGroup );

					$extends = array_merge( [ 'Contact', 'Activity' ], CRM_Contact_BAO_ContactType::basicTypes(), CRM_Contact_BAO_ContactType::subTypes() );
					if ( in_array( $customGroup['extends'], $extends ) ) {
						echo "<option value=\"custom_{$field['id']}\"{{#is auto_type value=\"custom_{$field['id']}\"}} selected=\"selected\"{{/is}}>" . sprintf( __( 'CiviCRM - %1$s - %2$s', 'caldera-forms-civicrm' ), $customGroup['title'], $field['label'] ) . "</option>";
					}
				}
			}
		}

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

		if ( ! empty( $field['config']['auto'] ) ) {

			// get all custom fields
			$customFields = $this->custom_fields_data_get();

			if ( $customFields && ! $customFields['is_error'] && $customFields['count'] != 0 ) {

				foreach ( $customFields['values'] as $key => $civiField ) {
					if ( in_array( $civiField['html_type'], $this->allowed_html_types ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
						switch ( $field['config']['auto_type'] ) {
							case 'custom_' . $civiField['id']:
								$customOptions = CRM_Core_OptionGroup::valuesByID( (int)$civiField['option_group_id'] );
								foreach ( $customOptions as $key => $value ) {
									$field['config']['option'][$key] = [
										'value' => $key,
										'label' => $value
									];
								}
								break;
						}
					}
				}
			}

		}

		return $field;

	}

	/**
	 * Retrieves custom field data from CiviCRM.
	 *
	 * @since 0.2
	 *
	 * @return array $presets The modified presets
	 */
	public function custom_fields_data_get() {

		// return data if it's already retrieved
		if ( ! empty( $this->custom_fields_data ) ) return $this->custom_fields_data;

		// get all custom fields
		$this->custom_fields_data = civicrm_api3( 'CustomField', 'get', [
			'sequential' => 1,
			'options' => [ 'limit' => 0 ],
			'is_active' => 1,
		] );

	}

}

