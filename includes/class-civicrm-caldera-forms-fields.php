<?php

/**
 * CiviCRM Caldera Forms Fields Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Fields {

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

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
		add_filter( 'caldera_forms_get_field_types', array( $this, 'custom_fields_register' ) );

		// adds custom fields options Presets
		add_filter( 'caldera_forms_field_option_presets', array( $this, 'custom_fields_options_presets' ) );

		// auto-populate CiviCRM fields
		add_filter( 'caldera_forms_render_get_field', array( $this, 'custom_fields_autopopulate' ), 20, 2 );
		add_action( 'caldera_forms_autopopulate_types', array( $this, 'custom_fields_autopopulate_options' ) );

		// auto-populate CiviCRM values
		add_filter( 'caldera_forms_render_get_field', array( $this, 'values_autopopulate' ), 20, 2 );
		add_action( 'caldera_forms_autopopulate_types', array( $this, 'values_autopopulate_options' ) );

	}

	/**
	 * Adds custom fields to Caldera UI.
	 *
	 * @uses 'caldera_forms_get_field_types' filter
	 *
	 * @since 0.2
	 *
	 * @param array $fieldtypes The existing fields configuration
	 * @return array $fieldtypes The modified fields configuration
	 */
	public function custom_fields_register( $fieldtypes ) {

		$fieldtypes['civicrm_country'] = array(
			'field'		 =>  __( 'CiviCRM Country', 'caldera-forms-civicrm' ),
			'file'		  =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/field.php',
			'category'	  =>  __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'description'   =>  __( 'CiviCRM Country dropdown', 'caldera-forms-civicrm' ),
			'setup'		 =>  array(
				'template'  =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/config.php',
				'preview'   =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/preview.php',
				'default'   =>  array(
					'placeholder' => __( 'Select a Country', 'caldera-forms-civicrm' ),
					'default' => CiviCRM_Caldera_Forms_Helper::get_civicrm_settings( 'defaultContactCountry' )
				),
				'not_supported' =>  array(
					'entry_list',
				)
			)
		);

		$fieldtypes['civicrm_state'] = array(
			'field'		 =>  __( 'CiviCRM State/Province', 'caldera-forms-civicrm' ),
			'file'		  =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/field.php',
			'category'	  =>  __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'description'   => __( 'CiviCRM State/Province dropdown', 'caldera-forms-civicrm' ),
			'setup'		 =>  array(
				'template'  =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/config.php',
				'preview'   =>  CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/preview.php',
				'default'   =>  array(
					'placeholder' => __( 'Select a State/Province', 'caldera-forms-civicrm' ),
					'default' => CiviCRM_Caldera_Forms_Helper::get_civicrm_settings( 'defaultContactStateProvince' )
				),
				'not_supported' =>  array(
					'entry_list',
				)
			)
		);

		return $fieldtypes;

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
		$customFields = civicrm_api3( 'CustomField', 'get', array(
			'sequential' => 1,
			'options' => array( 'limit' => 0 ),
			'is_active' => 1,
		));
		$htmlTypes = array( 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' );

		$custom = array();
		if ( $customFields && ! $customFields['is_error'] && $customFields['count'] != 0 ) {
			foreach ( $customFields['values'] as $key => $field ) {
				if ( in_array( $field['html_type'], $htmlTypes ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
					// get custom group
					$params['id'] = $field['custom_group_id'];
					$customGroup = array();
					CRM_Core_BAO_CustomGroup::retrieve( $params, $customGroup );

					// get options
					$customOptions = CRM_Core_OptionGroup::valuesByID( (int)$field['option_group_id'] );

					// contact types and activity for filtering
					$extends = array_merge( array( 'Contact', 'Activity' ), CRM_Contact_BAO_ContactType::basicTypes(), CRM_Contact_BAO_ContactType::subTypes());

					if ( in_array( $customGroup['extends'], $extends ) ) {
						$options = array();
						foreach ( $customOptions as $key => $value ) {
							$options[] = $key.'|'.$value;
						}
						$custom[$field['name']] = array(
							'name' => sprintf( __( 'CiviCRM - %1$s - %2$s', 'caldera-forms-civicrm' ), $customGroup['title'], $field['label'] ),
							'data' => $options,
						);
					}
				}
			}
		}

		$presets = array_merge( $custom, $presets );
		return $presets;

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
	public function custom_fields_autopopulate( $field, $form ) {

		if ( ! empty( $field['config']['auto'] ) ) {

			// get all custom fields
			$customFields = civicrm_api3( 'CustomField', 'get', array(
				'sequential' => 1,
				'options' => array('limit' => 0),
				'is_active' => 1,
			));

			if ( $customFields && ! $customFields['is_error'] && $customFields['count'] != 0 ) {
				$htmlTypes = array( 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' );

				foreach ( $customFields['values'] as $key => $civiField ) {
					if ( in_array( $civiField['html_type'], $htmlTypes ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
						switch ( $field['config']['auto_type'] ) {
							case 'custom_' . $civiField['id']:
								$customOptions = CRM_Core_OptionGroup::valuesByID( (int)$civiField['option_group_id'] );
								foreach ( $customOptions as $key => $value ) {
									$field['config']['option'][$key] = array(
										'value' => $key,
										'label' => $value
									);
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
	 * Adds CiviCRM custom fields options that extend Activities and any contact
	 * type to CF Autopopulate field type.
	 *
	 * @uses 'caldera_forms_autopopulate_types' action
	 *
	 * @since 0.2
	 */
	public function custom_fields_autopopulate_options() {

		// get all custom fields
		$customFields = civicrm_api3( 'CustomField', 'get', array(
			'sequential' => 1,
			'options' => array('limit' => 0),
			'is_active' => 1,
		));

		if ( $customFields && !$customFields['is_error'] && $customFields['count'] != 0 ) {
			$htmlTypes = array( 'Select', 'Radio', 'CheckBox', 'Multi-Select', 'AdvMulti-Select' );

			$custom = array();
			foreach ( $customFields['values'] as $key => $field ) {
				if ( in_array( $field['html_type'], $htmlTypes ) && isset( $field['option_group_id'] ) && ! empty( $field['option_group_id'] ) ) {
					// get custom group
					$params['id'] = $field['custom_group_id'];
					$customGroup = array();
					CRM_Core_BAO_CustomGroup::retrieve( $params, $customGroup );

					$extends = array_merge( array( 'Contact', 'Activity' ), CRM_Contact_BAO_ContactType::basicTypes(), CRM_Contact_BAO_ContactType::subTypes() );
					if ( in_array( $customGroup['extends'], $extends ) ) {
						echo "<option value=\"custom_{$field['id']}\"{{#is auto_type value=\"custom_{$field['id']}\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - {$customGroup['title']} - {$field['label']}" . "</option>";
					}
				}
			}
		}

	}

	/**
	 * Populates CiviCRM fields values for each CiviCRM CF Autopopulate field type.
	 *
	 * @uses 'caldera_forms_render_get_field' filter
	 *
	 * @since 0.1
	 *
	 * @param array $field The field to populate
	 * @param array $field The containing form
	 * @eturn array $field The populated field
	 */
	public function values_autopopulate( $field, $form ) {

		if ( ! empty( $field['config']['auto'] ) ) {

			switch ( $field['config']['auto_type'] ) {

				// Prefix
				case 'prefix_id':
					$prefix_id = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'prefix_id',
					));
					foreach ( $prefix_id['values'] as $index ) {
						//foreach ( $index as $key => $value ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
						//}
					}
					break;

				// Suffix
				case 'suffix_id':
					$suffix_id = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'suffix_id',
					));
					foreach ( $suffix_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Gender
				case 'gender_id':
					$prefix_id = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'gender_id',
					));
					foreach ( $prefix_id['values'] as $index ) {
						//foreach ( $index as $key => $value ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
						//}
					}
					break;

				// Communication Style
				case 'communication_style_id':
					$communication_style_id = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'communication_style_id',
					));
					foreach ( $communication_style_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Do Not Email
				case 'do_not_email':
					$do_not_email = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'do_not_email',
					));
					foreach ( $do_not_email['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Do Not Phone
				case 'do_not_phone':
					$do_not_phone = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'do_not_phone',
					));
					foreach ( $do_not_phone['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Do Not Mail
				case 'do_not_mail':
					$do_not_mail = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'do_not_mail',
					));
					foreach ( $do_not_mail['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Do Not SMS
				case 'do_not_sms':
					$do_not_sms = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'do_not_sms',
					));
					foreach ( $do_not_sms['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Do Not Trade
				case 'do_not_trade':
					$do_not_trade = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'do_not_trade',
					));
					foreach ( $do_not_trade['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Is Opt Out
				case 'is_opt_out':
					$is_opt_out = civicrm_api3( 'Contact', 'getoptions', array(
						'sequential' => 1,
						'field' => 'is_opt_out',
					));
					foreach ( $is_opt_out['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Country
				case 'country_id':
					$country_id = civicrm_api3( 'Country', 'get', array(
						'sequential' => 1,
						'options' => array('limit' => 0),
					));
					foreach ( $country_id['values'] as $key => $value ) {
							$field['config']['option'][$value['id']] = array(
								'value' => $value['id'],
								'label' => $value['name']
							);
					}
					break;

				// State/Province
				case 'state_province_id':
					$state_province_id = CiviCRM_Caldera_Forms_Helper::get_state_province();
					foreach ( $state_province_id as $key => $value ) {
							$field['config']['option'][$key] = array(
								'value' => $key,
								'label' => $value
							);
					}
					break;

				// Address Location Type
				case 'location_type_id':
					$location_type_id = civicrm_api3( 'Address', 'getoptions', array(
						'sequential' => 1,
						'field' => 'location_type_id',
					));
					foreach ( $location_type_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Email Location Type
				case 'e_location_type_id':
					$e_location_type_id = civicrm_api3( 'Email', 'getoptions', array(
						'sequential' => 1,
						'field' => 'location_type_id',
					));
					foreach ( $e_location_type_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Phone Location Type
				case 'p_location_type_id':
					$p_location_type_id = civicrm_api3( 'Phone', 'getoptions', array(
						'sequential' => 1,
						'field' => 'location_type_id',
					));
					foreach ( $p_location_type_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;

				// Phone Type
				case 'phone_type_id':
					$phone_type_id = civicrm_api3( 'Phone', 'getoptions', array(
						'sequential' => 1,
						'field' => 'phone_type_id',
					));
					foreach ( $phone_type_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;
			}

		}

		return $field;

	}

	/**
	 * Adds CiviCRM fields options to CF Autopopulate field type.
	 *
	 * @uses 'caldera_forms_autopopulate_types' action
	 *
	 * @since 0.2
	 */
	public function values_autopopulate_options() {

		// Individual Prefix
		echo "<option value=\"prefix_id\"{{#is auto_type value=\"prefix_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Individual Prefix', 'caldera-forms-civicrm' ) . "</option>";
		// Individual Suffix
		echo "<option value=\"suffix_id\"{{#is auto_type value=\"suffix_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Individual Suffix', 'caldera-forms-civicrm' ) . "</option>";
		// Individual Gender
		echo "<option value=\"gender_id\"{{#is auto_type value=\"gender_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Individual Gender', 'caldera-forms-civicrm' ) . "</option>";
		// Communication Style
		echo "<option value=\"communication_style_id\"{{#is auto_type value=\"communication_style_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Communication Style', 'caldera-forms-civicrm' ) . "</option>";
		// Do not Email
		echo "<option value=\"do_not_email\"{{#is auto_type value=\"do_not_email\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Do Not Email', 'caldera-forms-civicrm' ) . "</option>";
		// Do not Phone
		echo "<option value=\"do_not_phone\"{{#is auto_type value=\"do_not_phone\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Do Not Phone', 'caldera-forms-civicrm' ) . "</option>";
		// Do not Mail
		echo "<option value=\"do_not_mail\"{{#is auto_type value=\"do_not_mail\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Do Not Mail', 'caldera-forms-civicrm' ) . "</option>";
		// Do not SMS
		echo "<option value=\"do_not_sms\"{{#is auto_type value=\"do_not_sms\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Do Not SMS', 'caldera-forms-civicrm' ) . "</option>";
		// Do not Trade
		echo "<option value=\"do_not_trade\"{{#is auto_type value=\"do_not_trade\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Do Not Trade', 'caldera-forms-civicrm' ) . "</option>";
		// Is Opt Out
		echo "<option value=\"is_opt_out\"{{#is auto_type value=\"is_opt_out\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - No Bulk Emails (User Opt Out)', 'caldera-forms-civicrm' ) . "</option>";
		// Country
		echo "<option value=\"country_id\"{{#is auto_type value=\"country_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Country', 'caldera-forms-civicrm' ) . "</option>";
		// State/Provine
		echo "<option value=\"state_province_id\"{{#is auto_type value=\"state_province_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - State/Province', 'caldera-forms-civicrm' ) . "</option>";
		// Address Location Type
		echo "<option value=\"location_type_id\"{{#is auto_type value=\"location_type_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Address Location Type', 'caldera-forms-civicrm' ) . "</option>";
		// Email Location Type
		echo "<option value=\"e_location_type_id\"{{#is auto_type value=\"e_location_type_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Email Location Type', 'caldera-forms-civicrm' ) . "</option>";
		// Phone Location Type
		echo "<option value=\"p_location_type_id\"{{#is auto_type value=\"p_location_type_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Phone Location Type', 'caldera-forms-civicrm' ) . "</option>";
		// Phone Type
		echo "<option value=\"phone_type_id\"{{#is auto_type value=\"phone_type_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Phone Type', 'caldera-forms-civicrm' ) . "</option>";

	}

}
