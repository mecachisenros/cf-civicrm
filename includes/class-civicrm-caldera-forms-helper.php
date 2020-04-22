<?php

/**
 * CiviCRM Caldera Forms Helper Class.
 *
 * @since 0.1
 */
class CiviCRM_Caldera_Forms_Helper {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	 * Contact fields.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $contact_fields The contact fields
	 */
	public $contact_fields = [ 'prefix_id', 'first_name', 'last_name', 'middle_name', 'suffix_id', 'is_opt_out', 'nick_name', 'source', 'formal_title', 'job_title', 'gender_id', 'birth_date', 'email', 'current_employer', 'do_not_phone', 'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_trade', 'legal_identifier', 'legal_name', 'preferred_communication_method', 'preferred_language', 'preferred_mail_format', 'communication_style_id', 'household_name', 'organization_name', 'sic_code', 'image_URL' ];

	/**
	 * Activity fields.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $activity_fields The activity fields
	 */
	public $activity_fields = [ 'activity_type_id', 'phone_id', 'phone_number', 'status_id', 'priority_id', 'parent_id', 'is_test', 'medium_id', 'is_auto', 'is_current_revision', 'result', 'is_deleted', 'campaign_id', 'engagement_level', 'weight', 'id', 'original_id', 'relationship_id' ];

	/**
	 * Contribution fields.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var array $contribution_fields The contribution fields
	 */
	public $contribution_fields = [ 'financial_type_id', 'currency', 'total_amount', 'source', 'trxn_id', 'is_pay_later' ];

	/**
	 * Contribution_recur fields.
	 *
	 * @since 1.0.5
	 * @access public
	 * @var array $contribution_recur_fields The recurring contribution fields
	 */
	public $contribution_recur_fields = ['frequency_unit', 'frequency_interval', 'installments', 'payment_token_id', 'end_date', 'payment_processor_id'];

	/**
	 * Holds CiviCRM state/province data which only needs a single lookup.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $states The CiviCRM state/province data
	 */
	public $states;

	public $current_contact_data;

	/**
	 * CiviCRM tax and invoicing settings.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $tax_settings
	 */
	public $tax_settings;

	/**
	 * CiviCRM tax rates.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $tax_rates Holds tax rates in the form of [ <financial_type_id> => <tax_rate> ]
	 */
	public $tax_rates;

	/**
	 * Reference to the form fields set as price field options, indexed by processor id.
	 *
	 * @access public
	 * @since 1.0.5
	 * @var array $price_field_refs
	 */
	public $price_field_refs = [];

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Retrieve the select dropdown for contact links.
	 *
	 * @since 0.1
	 *
	 * @return str $contact_link The the select dropdown markup
	 */
	public function contact_link_field() {

		ob_start();
		$maxcontacts = caldera_forms_civicrm()->maxcontacts;
		echo '<select class="block-input field-config" name="{{_name}}[contact_link]">';
		for ($count = 1; $count <= $maxcontacts; $count++) {
            echo '<option value="' . $count . '" {{#is contact_link value=' . $count . '}}selected="selected"{{/is}}>' . sprintf(__('Contact %d', 'cf-civicrm'), $count) . '</option>';
		}
		echo '</select>';
		$contact_link = ob_get_contents();
		return $contact_link;
	}

	/**
	 * Get a CiviCRM Contact's custom fields.
	 *
	 * @since 0.1
	 *
	 * @return array $custom_fields The array of custom fields - e.g. ['custom_x' => 'Label of custom_x']
	 */
	public function get_contact_custom_fields() {

		$contact_types = civicrm_api3( 'ContactType', 'get', [
			'sequential' => 1,
			'is_active' => 1,
			'options' => [ 'limit' => 0 ],
		]);

		// Include Contact entity by default
		$types = [ 'Contact' ];
		foreach ( $contact_types['values'] as $key => $value ) {
			$types[] = $value['name'];
		}

		$extends = [ 'IN' => $types ];

		$custom_group = civicrm_api3( 'CustomGroup', 'get', [
			'sequential' => 1,
			'is_active' => 1,
			'extends' => apply_filters( 'cfc_custom_fields_contact_type', $extends ),
			'api.CustomField.get' => [ 'is_active' => 1, 'options' => [ 'limit' => 0 ] ],
			'options' => [ 'limit' => 0 ],
		]);

		$custom_fields = [];
		foreach ( $custom_group['values'] as $key => $value ) {
			foreach ( $value['api.CustomField.get']['values'] as $k => $v ) {
				$custom_fields['custom_' . $v['id']] = $v['label'];
			}
		}
		return $custom_fields;

	}

	/**
	 * Get the CiviCRM Contact ID for a WordPress user ID.
	 *
	 * @since 0.1
	 *
	 * @param int $id The numeric WordPress user ID
	 * @return int $contact_id The numeric CiviCRM Contact ID
	 */
	public function get_wp_civi_contact( $id ) {

		$params = [
			'sequential' => 1,
			'uf_id' => $id,
			'domain_id' => CRM_Core_BAO_Domain::getDomain()->id,
		];

		try {
			$wp_civicrm_contact = civicrm_api3( 'UFMatch', 'getsingle', $params );
		} catch ( CiviCRM_API3_Exception $e ) {
			Civi::log()->debug( 'Unable to match contact for user with id ' . $id );
		}

		return $wp_civicrm_contact['contact_id'];

	}

	/**
	 * Get a CiviCRM Contact.
	 *
	 * @since 0.1
	 *
	 * @param int $cid The numeric Contact ID
	 * @param bool|array The Contact data array, or 0 if none retrieved
	 */
	public function get_civi_contact( $cid ) {

		if ( $cid != 0 ) {

			$params = [
				'sequential' => 1,
				'id' => $cid,
			];

			try {
				$fields = civicrm_api3( 'Contact', 'getsingle', $params );
			} catch ( CiviCRM_API3_Exception $e ) {

			}

			// Custom fields
			$c_fields = implode( ',', array_keys( $this->plugin->helper->get_contact_custom_fields() ) );

			if ( empty( $c_fields ) ) return $fields;

			$params['return'] = $c_fields;

			try {
				$custom_fields = civicrm_api3( 'Contact', 'getsingle', $params );
			} catch ( CiviCRM_API3_Exception $e ) {

			}

			return array_merge( $fields, $custom_fields );

		} else {
			return 0;
		}
	}

	/**
	 * Get all CiviCRM Contact fields.
	 *
	 * @since 0.1
	 *
	 * @return array $contact_fields The array of content fields
	 */
	public function get_all_fields() {

		$contact_fields = civicrm_api3( 'Contact', 'getfields', [ 'sequential' => 1, ] );

		return $contact_fields['values'];

	}

	/**
	 * Get Countries from CiviCRM.
	 *
	 * @since 0.2
	 *
	 * @return array $states The array of countries
	 */
	public function get_countries() {

		// define basic API vars
		$api_vars = [
			'sequential' => 1,
			'options' => [ 'limit' => 0 ],
		];

		// get the countries enabled in CiviCRM localization settings
		$countries_enabled = $this->plugin->helper->get_civicrm_settings( 'countryLimit' );

		// limit to these if there are some defined
		if ( ! empty( $countries_enabled ) ) {
			$api_vars['id'] = [ 'IN' => $countries_enabled ];
		}

		// okay, let's hit the API
		return civicrm_api3( 'Country', 'get', $api_vars );

	}

	/**
	 * Get State/Province from CiviCRM.
	 *
	 * @since 0.1
	 *
	 * @return array $states The array of states
	 */
	public function get_state_province() {

		// send data back if already retrieved
		if ( isset( $this->states ) ) return $this->states;

		$query = 'SELECT name,id,country_id FROM civicrm_state_province';
		$dao = CRM_Core_DAO::executeQuery( $query );
		$this->states = [];

		while ( $dao->fetch() ) {
			$this->states[$dao->id] = [ 'name' => $dao->name, 'country_id' => $dao->country_id ];
		}

		foreach ( $this->states as $state_id => $state ) {
			if ( ! in_array( $state['country_id'], $this->get_civicrm_settings( 'countryLimit' ) ) ) {
				unset( $this->states[$state_id] );
			}
		}

		return $this->states;

	}

	/**
	 * Get CiviCRM settings.
	 *
	 * @since 0.1
	 *
	 * @param str $setting The name of the setting to be returned
	 * @return array $settings The requested settings
	 */
	public function get_civicrm_settings( $setting ){

		$settings = civicrm_api3( 'Setting', 'getvalue', [
			'sequential' => 1,
			'name' => $setting,
		] );

		return $settings;

	}

	/**
	 * Get Deduplicate rules.
	 *
	 * @since 0.1
	 *
	 * @return array $dedupe_rules The deduplicate rules
	 */
	public function get_dedupe_rules() {

		$dedupe_rules['Organization'] = CRM_Dedupe_BAO_RuleGroup::getByType( 'Organization' );
		$dedupe_rules['Individual'] = CRM_Dedupe_BAO_RuleGroup::getByType( 'Individual' );
		$dedupe_rules['Household'] = CRM_Dedupe_BAO_RuleGroup::getByType( 'Household' );

		return $dedupe_rules;

	}

	/**
	 * Get the 'extends' value for a given custom field, ie 'custom_1'
	 *
	 * @since 0.1.1
	 *
	 * @param int|str $custom_id The numeric ID of the custom field
	 * @return str $result The 'extends' value for the custom field
	 */
	public function custom_field_extends( $custom_id ) {

		$custom_id = str_replace( 'custom_', '', $custom_id );
		$id = (int)$custom_id;

		try {
			$result = civicrm_api3( 'CustomField', 'getsingle', [
				'sequential' => 1,
				'id' => $id,
				'api.CustomGroup.getsingle' => [
					'id' => '$value.custom_group_id',
					'return' => [ 'extends_entity_column_value', 'extends' ]
				],
			] );
		} catch( CiviCRM_API3_Exception $e ) {

		}

		if ( isset( $result['api.CustomGroup.getsingle']['extends_entity_column_value'] ) ) {
			return implode( ',', $result['api.CustomGroup.getsingle']['extends_entity_column_value'] );
		} else {
			return $result['api.CustomGroup.getsingle']['extends'];
		}

	}

	/**
	 * Helper method to map fields values to processor
	 *
	 * @since 0.4
	 *
	 * @param array $config The processor settings
	 * @param array $form The form settings
	 * @param array $form_values The submitted form values
	 * @param string $processor The processor key, only necessary for the Contact processor class
	 * @return array $form_values
	 */
	public function map_fields_to_processor( $config, $form, &$form_values, $processor = null ){
		foreach ( ( $processor ? $config[$processor] : $config ) as $civi_field => $field_id ) {
			if ( ! empty( $field_id ) ) {

				if ( is_array( $field_id ) ) continue;

				// do bracket magic tag
				if ( strpos( $field_id, '{' ) !== false ) {
					$mapped_field = Caldera_Forms_Magic_Doer::do_bracket_magic( $field_id, $form, NULL, NULL, NULL );

				} elseif ( strpos( $field_id, '%' ) !== false && substr_count( $field_id, '%' ) > 2 ) {

					// multiple fields mapped
					// explode and remove empty indexes
					$field_slugs = array_filter( explode( '%', $field_id ) );

					$mapped_fields = [];
					foreach ( $field_slugs as $k => $slug ) {
						$field = Caldera_Forms::get_field_by_slug( $slug, $form );
						$mapped_fields[] = Caldera_Forms::get_field_data( $field['ID'], $form );
					}

					$mapped_fields = array_filter( $mapped_fields );
					// expect one value, return first value
					$mapped_field = reset( $mapped_fields );

				} else {

					// Get field by ID or slug
					$field = $mapped_field =
						Caldera_Forms_Field_Util::get_field( $field_id, $form ) ?
						Caldera_Forms_Field_Util::get_field( $field_id, $form ) :
						Caldera_Forms::get_field_by_slug(str_replace( '%', '', $field_id ), $form );

					// Get field data
					$mapped_field = Caldera_Forms::get_field_data( $mapped_field['ID'], $form );

					// if not a magic tag nor field id, must be a fixed value
					// $mapped_field = $mapped_field ? $mapped_field : $field_id;

				}

				/**
				 * Filter mapped field value, fires for every processor field.
				 *
				 * @since  0.4.4
				 *
				 * @param string|int $mapped_field The mapped value
				 * @param string $civi_field The field for an entity i.e. 'contact_id', 'current_employer', etc.
				 * @param array $field The field config
				 * @param array $config processor config
				 * @param array $form Form config
				 */
				$mapped_field = apply_filters( 'cfc_filter_mapped_field_to_processor', $mapped_field, $civi_field, $field, $config, $form );

				if( ! empty( $mapped_field ) || $mapped_field === '0'){

					if ( $processor ) {
						$form_values[$processor][$civi_field] = $mapped_field;
					} else {
						$form_values[$civi_field] = $mapped_field;
					}
				}
			}
		}

		return $form_values;
	}

	/**
	 * Helper method to map CiviCRM data to form fields (autopopulate/prerender)
	 *
	 * @since 0.4
	 *
	 * @param array $config The processor settings
	 * @param array $form The form settings
	 * @param array $ignore_fields The fields to be ignored during data mapping
	 * @param array $entity The entity being mapped with its values, i.e. Contact, Address, etc
	 * @return array $processor The processor key, only necessary for the Contact processor class
	 */
	public function map_fields_to_prerender( $config, &$form, $ignore_fields, $entity, $processor = null ){

		foreach ( ( $processor ? $config[$processor] : $config ) as $civi_field => $field_id ) {
			if ( ! empty( $field_id ) && ! in_array( $civi_field, $ignore_fields ) ) {

				// Get field by ID or slug
				$field =
					Caldera_Forms_Field_Util::get_field( $field_id, $form ) ?
					Caldera_Forms_Field_Util::get_field( $field_id, $form ) :
					Caldera_Forms::get_field_by_slug(str_replace( '%', '', $field_id ), $form );

				// don't prerender hidden field values unless pre_render enable
				if ( $field['type'] == 'hidden' && ! isset( $field['config']['pre_render'] ) ) continue;

				$value = ! empty( $entity[$civi_field] ) ? $entity[$civi_field] : '';

				// If the CF field is a date picker, convert the date value to the date picker's format.
				if ( $field['type'] == 'date_picker' && ! empty( $value ) ) {
					$format = $this->translate_date_picker_format( $field['config']['format'] );
					$value = date_create( $value )->format( $format );
				}

				/**
				 * Filter prerenderd value (default value), fires for every processor field.
				 *
				 * @since  0.4.4
				 *
				 * @param string|int $value The default value
				 * @param string $civi_field The field for an entity i.e. 'contact_id', 'current_employer', etc.
				 * @param array $field The field config
				 * @param array $entity The current entity, i.e. Contact, Address, etc
				 * @param array $config processor config
				 */
				$form['fields'][$field['ID']]['config']['default'] = apply_filters( 'cfc_filter_mapped_field_to_prerender', $value, $civi_field, $field, $entity, $config );

				if ( $field['type'] == 'radio' ) {
					$options = Caldera_Forms_Field_Util::find_option_values( $field );
					$form['fields'][$field['ID']]['config']['default'] = array_search( $value, $options );
				}
			}
		}

		return $form;
	}

	/**
	 * Translate Caldera Forms date picker formats to PHP date formats.
	 *
	 * @since 1.0.2
	 *
	 * @param string $date_picker_format The Caldera Forms date picker format
	 */
	public function translate_date_picker_format( $date_picker_format ){
		// Translate each token used in the CF date picker format to the corresponding PHP format character.
		$token_map = [
			'yyyy' => 'Y',
			'yy'   => 'y',
			'MM'   => 'F',
			'M'    => 'M',
			'mm'   => 'm',
			'm'    => 'n',
			'DD'   => 'l',
			'D'    => 'D',
			'dd'   => 'd',
			'd'    => 'j',
		];

		return strtr( $date_picker_format, $token_map );
	}

	/**
	 * Get CiviCRM enabled extensions.
	 *
	 * @since 0.4.1
	 * @return array $enabled_extensions Array of enabled extensions containing the 'key'
	 */
	public function get_enabled_extensions(){
		try {
			$result = civicrm_api3( 'Extension', 'get', [
				'sequential' => 1,
				'status' => 'installed',
				'statusLabel' => 'Enabled',
				'options' => [ 'limit' => 0 ],
			] );
		} catch ( CiviCRM_API3_Exception $e ) {

		}

		$enabled_extensions = [];
		if( $result['is_error'] == 0 ){
			foreach ( $result['values'] as $key => $extension) {
				$enabled_extensions[] = $extension['key'];
			}
			return $enabled_extensions;
		}

		return false;
	}

	/**
	 * Create entity file.
	 *
	 * @since 0.4.2
	 * @param string $entity The entity table name
	 * @param int $entity_id The entity id
	 * @return int $file_id The file id
	 */
	public function create_civicrm_entity_file( $entity, $entity_id, $file_id ){
		$entityFileDAO = new CRM_Core_DAO_EntityFile();
		$entityFileDAO->entity_table = $entity;
		$entityFileDAO->entity_id = $entity_id;
		$entityFileDAO->file_id = $file_id;
		$entityFileDAO->save();
	}

	/**
	 * Handle core entities file attachments.
	 *
	 * @since 0.4.4
	 *
	 * @param string $entity Core entity table, ie 'civicrm_activity', 'civicrm_note'
	 * @param int $entity_id Entity ID
	 * @param string $field_id Form field ID
	 * @param array $form The form config
	 */
	public function handle_file_attachments_core( $entity, $entity_id, $field_id, $form ) {

		// get field type for the mapped file field
		$field_type = Caldera_Forms_Field_Util::get_type( $field_id, $form );

		if ( $field_type == 'file' ) { // file type

			$file_fields = $this->plugin->fields->field_objects['civicrm_file']->file_fields;

			if ( ! empty( $file_fields[$field_id]['files'] ) ) {
				foreach ( $file_fields[$field_id]['files'] as $file_id => $file ) {
					$this->create_civicrm_entity_file(
						$entity,
						$entity_id,
						$file_id
					);
				}
			}

		} elseif ( in_array( $field_type, ['advanced_file', 'cf2_file'] ) ) { // advanced file
			global $transdata;
			// get civicrm file ids from $transdata
			if ( ! empty( $transdata['data'][$field_id] ) ) {
				foreach ( $transdata['data'][$field_id] as $civi_file_id ) {
					$this->create_civicrm_entity_file(
						$entity,
						$entity_id,
						$civi_file_id
					);
				}
			}
		}
	}

	/**
	 * Get field value by slug or magic tag slug.
	 *
	 * @since 0.4.4
	 *
	 * @param string $slug Field slug or slug magic tag ie %field_slug%
	 * @param array $form Form config
	 * @return string|array The field value
	 */
	public function get_field_data_by_slug( $slug, $form ) {
		$slug = strpos( $slug, '%' ) !== false ? str_replace( '%', '', $slug ) : $slug;
		$field = Caldera_Forms::get_field_by_slug( $slug, $form );
		return Caldera_Forms::get_field_data( $field['ID'], $form );
	}

	/**
	 * Get CiviCRM tax and invoicing settings.
	 *
	 * @since 1.0
	 * @return array $tax_settings
	 */
	public function get_tax_settings() {
		if ( is_array( $this->tax_settings ) ) return $this->tax_settings;
		$this->tax_settings = $this->get_civicrm_settings( 'contribution_invoice_settings' );
		return $this->tax_settings;
	}

	/**
	 * Get CiviCRM tax invoicing setting.
	 *
	 * @since 1.0.4
	 * @return bool True if invoicing is set, false otherwise.
	 */
	public function get_tax_invoicing() {
		$tax_settings = $this->get_tax_settings();
		if ( ! array_key_exists( 'invoicing', $tax_settings ) ) {
			return false;
		}
		if ( empty( $tax_settings['invoicing'] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Get CiviCRM tax rates.
	 *
	 * @since 1.0
	 * @return array|bool Array of tax rates in the form of [ <financial_type_id> => <tax_rate> ]
	 */
	public function get_tax_rates() {

		if ( is_array( $this->tax_rates ) ) return $this->tax_rates;

		$tax_financial_accounts = civicrm_api3( 'EntityFinancialAccount', 'get', [
			'return' => [
				'id',
				'entity_table',
				'entity_id',
				'account_relationship',
				'financial_account_id',
				'financial_account_id.financial_account_type_id',
				'financial_account_id.tax_rate'
			],
			'financial_account_id.is_active' => 1,
			'financial_account_id.is_tax' => 1,
			'options' => [ 'limit' => 0 ]
		] );

		if ( $tax_financial_accounts['count'] ) {
			// buils tax rates
			$this->tax_rates = array_reduce( $tax_financial_accounts['values'], function( $tax_rates, $financial_account ) {
				$tax_rates[$financial_account['entity_id']] = $financial_account['financial_account_id.tax_rate'];
				return $tax_rates;
			}, [] );

			return $this->tax_rates;

		}

		return false;
	}

	/**
	 * Calculate percentage for a given amount.
	 *
	 * @since 1.0
	 * @param string $amount The amount
	 * @param string $percentage The percentage
	 * @return string $amount Calculated percentage amount
	 */
	public function calculate_percentage( $amount, $percentage ) {
		return ( $percentage / 100 ) * $amount;
	}

	/**
	 * Format tax label as per CiviCRM.
	 *
	 * @param string $label The label
	 * @param string $amount The amount
	 * @param string $tax_amount The tax amount
	 * @return string $label The formated label
	 */
	public function format_tax_label( $label, $amount, $tax_amount, $currency = false ) {

		$tax_settings = $this->get_tax_settings();
		$tax_term = $tax_settings['tax_term'];
		$taxed_amount = $this->format_money( $amount + $tax_amount, $currency );
		$tax_amount = $this->format_money( $tax_amount, $currency );
		$amount = $this->format_money( $amount, $currency );

		$format = [
			'Do_not_show' => sprintf( '%1$s - %2$s', $label, $taxed_amount ),
			'Inclusive' => sprintf( '%1$s - %2$s (includes %3$s of %4$s)', $label, $taxed_amount, $tax_term, $tax_amount ),
			'Exclusive' => sprintf( '%1$s - %2$s + %3$s %4$s', $label, $amount, $tax_amount, $tax_term )
		];

		return $format[$tax_settings['tax_display_settings']];

	}

	/**
	 * Format money, as per CiviCRM settings.
	 *
	 * @since 1.0
	 * @param string $amount The amount
	 * @param string $currency Optional, the currency
	 * @return string $formated_amount The formated amount
	 */
	public function format_money( $amount, $currency = false ) {
		return CRM_Utils_Money::format( $amount, $currency );
	}

	/**
	 * Get price sets.
	 *
	 * @since 0.4.4
	 * @return array $price_sets The active price sets with their corresponding price fields and price filed values
	 */
	public function get_price_sets() {

		// get tax settings
		$tax_settings = $this->get_tax_settings();
		// get tax rates
		$tax_rates = $this->get_tax_rates();

		$price_set_params = [
			'sequential' => 1,
			'is_active' => 1,
			'is_reserved' => 0,
			'options' => [ 'limit' => 0 ],
			'api.PriceField.get' => [
				'sequential' => 0,
				'price_set_id' => "\$value.id",
				'is_active' => 1,
				'options' => [ 'limit' => 0 ],
			],
		];


		try {
			$result_price_sets = civicrm_api3( 'PriceSet', 'get', $price_set_params );
		} catch ( CiviCRM_API3_Exception $e ) {
			return [ 'note' => $e->getMessage(), 'type' => 'error' ];
		}

		try {
			$all_price_field_values = civicrm_api3( 'PriceFieldValue', 'get', [
				'sequential' => 0,
				'is_active' => 1,
				'options' => [ 'limit' => 0 ],
			] );
		} catch ( CiviCRM_API3_Exception $e ) {
			return [ 'note' => $e->getMessage(), 'type' => 'error' ];
		}

		// false if no price field values or price sets
		if ( ! $all_price_field_values['count'] || ! $result_price_sets['count'] ) return false;

		$price_field_values = [];
		foreach ( $all_price_field_values['values'] as $id => $price_field_value ) {
			$price_field_value['amount'] = $price_field_value['amount'];
			$price_field_values[$id] = $price_field_value;
		}

		$price_sets = [];
		foreach ( $result_price_sets['values'] as $key => $price_set ) {
			$price_set['price_set_id'] = $price_set_id = $price_set['id'];
			$price_set['price_fields'] = $price_set['api.PriceField.get']['values'];
			foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
				$price_set['price_fields'][$price_field_id]['price_field_id'] = $price_field_id;
				foreach ( $price_field_values as $value_id => $price_field_value) {
					$price_field_value['price_field_value_id'] = $value_id;
					if ( $price_field_id == $price_field_value['price_field_id'] ) {
						if ( $this->get_tax_invoicing() && $tax_rates && array_key_exists( $price_field_value['financial_type_id'], $tax_rates ) ) {
							$price_field_value['tax_rate'] = $tax_rates[$price_field_value['financial_type_id']];
							$price_field_value['tax_amount'] = $this->calculate_percentage( $price_field_value['amount'], $price_field_value['tax_rate'] );
						}
						$price_set['price_fields'][$price_field_id]['price_field_values'][$value_id] = $price_field_value;
					}
				}
			}
			unset( $price_set['id'], $price_set['api.PriceField.get'] );
			$price_sets[$price_set_id] = $price_set;
		}

		return $price_sets;
	}

	/**
	 * Get cached active price sets with their corresponding price fields and price filed values.
	 *
	 * @since 0.4.4
	 *
	 * @return array|false $price_sets
	 */
	public function cached_price_sets() {
		$price_sets = get_transient( 'cfc_civicrm_price_sets' );
		if ( $price_sets ) return $price_sets;

		// set transient only if we have price sets
		if ( $this->get_price_sets() ) {
			if ( set_transient( 'cfc_civicrm_price_sets', $this->get_price_sets(), DAY_IN_SECONDS ) )
				return get_transient( 'cfc_civicrm_price_sets' );
		}

		return false;

	}

	/**
	 * Get Price Field Value by id.
	 *
	 * @since  0.4.4
	 *
	 * @param  int $id Price Field Value id
	 * @return array $price_field_value The Price Field Value
	 */
	public function get_price_field_value( $id ) {
		// when using a checkbox the value that gets passed is an array
		if ( is_array( $id ) )
			$id = array_pop( $id );

		// $id = str_replace( 'price_field_value_id_', '', $id );

		// single option
		if ( ! strpos( $id, ',' ) ) {
			$price_field_value = $this->get_price_set_column_by_id( $id, 'price_field_value' );
		} else {
			// multiple options
			$id = explode( ', ', $id );

			$price_field_value = array_reduce( $id, function( $options, $option_id ) {
				$option_id = ( int ) $option_id;
				$options[$option_id] = $this->get_price_set_column_by_id( $option_id, 'price_field_value' );
				return $options;
			}, [] );

		}

		// filter price field value
		$price_field_value = apply_filters( 'cfc_filter_price_field_value_get', $price_field_value, $id );

		return $price_field_value;
	}

	/**
	 * Get price_set/price_field/price_field_value by id specifing the column name.
	 *
	 * @since 1.0
	 * @param int $id The entity id
	 * @param string $column_name The column name, price_set|price_field|price_field_value
	 * @return array $column The requested entity or false
	 */
	public function get_price_set_column_by_id( $id, $column_name ) {

		$price_sets = $this->cached_price_sets();

		if ( $column_name == 'price_set' && array_key_exists( $id, $price_sets ) ) {
			$column = $price_sets[$id];
		}

		if ( $column_name == 'price_field' ) {
			foreach ( $price_sets as $price_set_id => $price_set ) {
				foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
					if ( array_key_exists( $id, $price_set['price_fields'] ) )
						$column = $price_set['price_fields'][$id];
				}
			}
		}

		if ( $column_name == 'price_field_value' ) {
			foreach ( $price_sets as $price_set_id => $price_set ) {
				foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
					foreach ( $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) {
						if ( array_key_exists( $id, $price_field['price_field_values'] ) )
							$column = $price_field['price_field_values'][$id];
					}
				}
			}
		}

		if ( isset( $column ) ) return $column;

		return false;

	}

	/**
	 * Get membership types assocaited to an Organizaion.
	 *
	 * @since 0.4.4
	 *
	 * @param int $cid Organization contact id
	 * @return array|boolean The membership types for that organization or false
	 */
	public function get_organization_membership_types( $cid ) {
		$membership_types = civicrm_api3( 'MembershipType', 'get', [
		  'return' => ['id'],
		  'member_of_contact_id' => $cid,
		] );

		if ( ! $membership_types['is_error'] && $membership_types['count'] ) {
			$types = [];
			foreach ( $membership_types['values'] as $id => $type ) {
				$types[] = $id;
			}
		}

		if ( isset( $types ) && ! empty( $types ) )
			return $types;

		return false;
	}

	/**
	 * Get current memberships for a conatct.
	 *
	 * @since 0.4.4
	 * @param int $cid Contact id
	 * @param  int|string $membership_type Membership type
	 * @param string $sort Sort by join date ASC or DESC
	 * @return array|boolean The current membetships or false
	 */
	public function get_membership( $cid = false, $membership_type = false, $sort = 'DESC', $skip_status = false ) {

		if ( ! $cid ) return false;

		$params = [
			'sequential' => 1,
			'contact_id' => $cid,
			'options' => [ 'sort' => 'join_date ' . $sort, 'limit' => 1 ],
			'is_test' => 0,
		];

		if ( ! $skip_status )
			$params['status_id'] = [ 'IN' => apply_filters( 'cfc_current_membership_get_status', [ 'New', 'Current', 'Grace' ] ) ];

		if ( $membership_type )
			$params['membership_type_id'] = $membership_type;

		$memberships = civicrm_api3( 'Membership', 'get', $params );

		if ( ! $memberships['is_error'] && $memberships['count'] )
			return array_pop( $memberships['values'] );

		return false;
	}

	/**
	 * Dedupe CiviCRM Contact.
	 *
	 * @since 0.4.4
	 * @param array $contact Contact data
	 * @param string $contact_type Contact type
	 * @param int $dedupe_rule_id Dedupe Rule ID
	 * @return int $contact_id The contact id
	 */
	public function civi_contact_dedupe( $contact, $contact_type, $dedupe_rule_id ) {
		// Dupes params
		$dedupeParams = CRM_Dedupe_Finder::formatParams( $contact, $contact_type );
		$dedupeParams['check_permission'] = FALSE;

		// Check dupes
		$cids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams, $contact_type, NULL, [], $dedupe_rule_id );
		$cids = array_reverse( $cids );

		return $cids ? array_pop( $cids ) : 0;
	}

	/**
	 * Get current Contact data.
	 *
	 * Checks for a valid checksum, and if the user is logged in,
	 * logged in user data has precedence over checksum.
	 *
	 * @since 0.4.4
	 * @return array|boolean $contact The Contact data, false otherwise
	 */
	public function current_contact_data_get() {

		if ( ! empty( $this->current_contact_data ) ) return $this->current_contact_data;

		$contact = false;

		// checksum links first
		if ( isset( $_GET['cid'] ) ) {

			$cid = $_GET['cid'];
			$cs = isset( $_GET['cs'] ) ? $_GET['cs'] : '';

			if( $cid ) {
				// Check for contact permissions or valid checksum
				$valid_user = CRM_Contact_BAO_Contact_Permission::allow($cid, CRM_Core_Permission::EDIT)
				            || ($cs && CRM_Contact_BAO_Contact_Utils::validChecksum( $cid, $cs ));

				if ( $valid_user )
					$contact = $this->plugin->helper->get_civi_contact( $cid );
			}

		}
		// Try logged in user if no cid supplied
		elseif ( is_user_logged_in() ) {
			$contact = $this->get_current_contact();
		}

		$this->current_contact_data = $contact;

		return $contact;

	}

	/**
	 * Get current Contact data.
	 *
	 * @since 0.4.4
	 * @return array|boolean $contact The contact data or false
	 */
	public function get_current_contact() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$current_user = $this->get_wp_civi_contact( $current_user->ID );
			return $this->get_civi_contact( $current_user );
		}
		return false;
	}

	/**
	 * Get a Participant custom fields.
	 *
	 * @since 1.0
	 * @return array $custom_fields The array of custom fields - e.g. ['custom_x' => 'Label of custom_x']
	 */
	public function get_participant_custom_fields() {

		try {
			$custom_groups = civicrm_api3( 'CustomGroup', 'get', [
				'sequential' => 1,
				'is_active' => 1,
				'extends' => 'Participant',
				'api.CustomField.get' => [ 'is_active' => 1, 'options' => [ 'limit' => 0 ] ],
				'options' => [ 'limit' => 0 ],
			] );
		} catch ( CiviCRM_API3_Exception $e ) {
			return [ 'note' => $e->getMessage(), 'type' => 'error' ];
		}

		$custom_fields = [];
		foreach ( $custom_groups['values'] as $key => $custom_group ) {
			foreach ( $custom_group['api.CustomField.get']['values'] as $k => $custom_field ) {
				$custom_fields['custom_' . $custom_field['id']] = [
					'label' => $custom_field['label'],
					'extends_entity_column_id' => $custom_group['extends_entity_column_id'],
					'extends_entity_column_value' => $custom_group['extends_entity_column_value']
				];
			}
		}

		return $custom_fields;

	}

	/**
	 * Get processor by type.
	 *
	 * @since 1.0
	 * @param string $processor_type The processor type
	 * @param array $form Form config
	 * @return array $processors The processors config
	 */
	public function get_processor_by_type( $processor_type, $form ) {
		// get form processors
		$processors = Caldera_Forms::get_processor_by_type( $processor_type, $form );
		// filter out non associative keys
		if ( $processors )
			return array_filter( $processors, function( $processor, $id ) {
				return $id === $processor['ID'];
			}, ARRAY_FILTER_USE_BOTH );

		return false;
	}

	/**
	 * Get processor id from magic tag.
	 *
	 * @since 1.0
	 * @since 1.0.5 Addedd $return_config optional param to return the processor config
	 * @param string $magic_tag The processor_id magig tag
	 * @param array|boolean $form The form config or false
	 * @param bool $return_config Whether to return the processor config array or the processor id string
	 * @return string|array|boolean $processor_id The processor_id, the processor config array, or false
	 */
	public function get_processor_from_magic( $magic_tag, $form = false, $return_config = false ) {

		if ( ! is_string( $magic_tag ) ) return false;

		if ( strpos( $magic_tag, '{' ) === false ) return false;

		if ( strpos( $magic_tag, 'processor_id' ) === false ) return false;

		// clean up magic tag
		$magic_tag = str_replace( [ '{', '}' ], '', $magic_tag );
		// get parts
		$parts = explode( ':', $magic_tag );

		if( ! $form ) global $form;

		// handle cf select field ({{{_field}}}) magic tags
		// values which render as fp_123456:processor_id
		if ( false !== strpos( $parts[0], 'fp_' ) ) {
			$processor = $form['processors'][$parts[0]];
			$processor_id = $parts[0];
		} elseif ( count( $parts ) > 2 ) {
			// if form has more than one processor of same type
			// the magic tag has the format of processor_type:processor_id:<id>
			// otherwise the format is processor_type:processor_id
			$processor_id = $parts[2];
			$processor = $form['processors'][$processor_id];
		} else {
			// one processor, the format is processor_type:processor_id
			$processor = $this->get_processor_by_type( $parts[0], $form );
			$processor_id = $processor['ID'];
		}

		if ( $return_config ) {
			return $processor;
		} else {
			return $processor_id;
		}

	}

	/**
	 * Get Case custom fields.
	 *
	 * @since 1.0.3
	 * @return array $custom_fields The array of custom fields
	 */
	public function get_case_custom_fields() {

		try {
			$custom_groups = civicrm_api3( 'CustomGroup', 'get', [
				'sequential' => 1,
				'is_active' => 1,
				'extends' => 'Case',
				'api.CustomField.get' => [ 'is_active' => 1, 'options' => [ 'limit' => 0 ] ],
				'options' => [ 'limit' => 0 ],
			] );
		} catch ( CiviCRM_API3_Exception $e ) {
			return [ 'note' => $e->getMessage(), 'type' => 'error' ];
		}

		$custom_fields = [];
		foreach ( $custom_groups['values'] as $key => $custom_group ) {
			foreach ( $custom_group['api.CustomField.get']['values'] as $k => $custom_field ) {
				$custom_fields['custom_' . $custom_field['id']] = [
					'label' => $custom_group['title'] . ' - ' . $custom_field['label'],
					'extends_entity_column_id' => $custom_group['extends_entity_column_id'],
					'extends_entity_column_value' => $custom_group['extends_entity_column_value']
				];
			}
		}

		return $custom_fields;

	}

	/**
	 * Retrieves the related contacts for a given contact.
	 *
	 * @since 1.0.4
	 * @param array|int $contact The contact data array or the contact_id
	 * @param array $form The form config
	 * @return array|bool $related_contacts The related contacts or false
	 */
	public function get_contact_related_contacts( $contact, $form ) {

		// get relationship processors
		$relationship_configs = $this->get_processor_by_type( 'civicrm_relationship', $form );

		if ( empty( $relationship_configs ) ) return false;

		$contact_configs = $this->get_processor_by_type( 'civicrm_contact', $form );

		// processor id and its contact link
		$contact_link_relations = array_reduce( $contact_configs, function( $relations, $processor ) {
			$relations[$processor['ID']] = $processor['config']['contact_link'];
			return $relations;
		}, [] );

		$contact_id = is_array( $contact ) ? $contact['id'] : $contact;

		return array_reduce( $relationship_configs, function( $contacts, $processor ) use ( $contact_id, $contact_link_relations ) {

			if ( empty( $processor['runtimes'] ) ) return $contacts;

			$relationship = civicrm_api3( 'Relationship', 'get', [
				'contact_id_a' => $contact_id,
				'contact_id_b' => $contact_id,
				'relationship_type_id' => $processor['config']['relationship_type'],
				'is_active' => 1,
				'options' => [
					'or' => [['contact_id_a', 'contact_id_b']],
					'limit' => 1,
					'sort' => 'id desc'
				]
			] );

			// bail if no relationship or we have more than one relationship
			if ( ! $relationship['count'] || $relationship['count'] > 1 ) return $contacts;

			$result = $relationship = $relationship['values'][$relationship['id']];
			// unset relationship possible collisioning ids
			unset( $result['id'], $result['relationship_type_id'], $result['case_id'] );
			// get 'opposite' realtion, contact_id_a <=> contact_id_b
			$relation = array_search( $contact_id , $result ) == 'contact_id_a'
				? 'contact_id_b'
				: 'contact_id_a';

			$contact_processor_id = array_search(
				$processor['config'][ str_replace( '_id', '', $relation ) ], // relation is stored as contact_a|b in the processor, stupid me
				$contact_link_relations
			);

			$contacts[$contact_processor_id] = $this->get_civi_contact( $relationship[$relation] );

			return $contacts;

		}, [] );

	}

	/**
	 * Build Price Field fields references from Line Item processors
	 * for paid events, memberships, and line items.
	 *
	 * @since 1.0.5
	 * @param array $form The form config
	 * @return array|boolean $price_field_ref References to [ <processor_id> => <field_id> ], or false
	 */
	public function build_price_field_refs( $form ) {

		if ( ! empty( $this->price_field_refs ) ) return $this->price_field_refs;

		// line item processors
		$line_items = $this->plugin->helper->get_processor_by_type( 'civicrm_line_item', $form );

		if ( empty( $line_items ) ) return [];

		$rendered_fields = array_reduce( $form['fields'], function( $fields, $field ) use ( $form ) {
			$config = Caldera_Forms_Field_Util::get_field( $field['ID'], $form, true );
			$fields[] = $config['slug'];
			return $fields;
		}, [] );

		$this->price_field_refs = array_reduce( $line_items, function( $refs, $line_item ) use ( $form, $rendered_fields ) {

			if ( empty( $line_item['config']['entity_table'] ) ) return $refs;

			$price_field_slug = $line_item['config']['price_field_value'];

			if ( strpos( $price_field_slug, '%' ) !== false && substr_count( $price_field_slug, '%' ) > 2 ) {

				$price_field_slug = array_filter( explode( '%', $price_field_slug ) );

				$price_field_slug = array_intersect( $price_field_slug, $rendered_fields );

			} else {

				$price_field_slug = str_replace( '%', '', $price_field_slug );

			}

			$processor_id = $line_item['ID'];

			// price_field field config
			if ( is_array( $price_field_slug ) ) {

				if ( count( $price_field_slug ) > 1 ) {
					foreach ( $price_field_slug as $key => $field_id ) {

						if ( $key == 0 ) {

							$price_field_field = Caldera_Forms_Field_Util::get_field_by_slug( $field_id, $form );

							$refs[$processor_id] = $price_field_field['ID'];
						} else {

							$price_field_field = Caldera_Forms_Field_Util::get_field_by_slug( $field_id, $form );

							$refs[$processor_id . '#' . $key ] = $price_field_field['ID'];
						}

					}
				} else {

					$price_field_slug = array_pop( $price_field_slug );

					$price_field_field = Caldera_Forms_Field_Util::get_field_by_slug( $price_field_slug, $form );

					$refs[$processor_id] = $price_field_field['ID'];
				}

			} else {

				$price_field_field = Caldera_Forms_Field_Util::get_field_by_slug( $price_field_slug, $form );

				$refs[$processor_id] = $price_field_field['ID'];

			}

			return $refs;

		}, [] );

		return $this->price_field_refs;

	}

	/**
	 * Get entity ids (events, memberships, or contributions) for a set of processors.
	 *
	 * @since 1.0.5
	 * @param array $form Array holding the processors config
	 * @return array|boolean $entity_ids References to [ <processor_id> => <entity_id> ], or false
	 */
	public function get_entity_ids_from_line_items( $form ) {

		$line_items = $this->get_processor_by_type( 'civicrm_line_item', $form );

		if ( empty( $line_items ) ) return [];

		return array_reduce( $line_items, function( $ids, $processor ) use ( $form ) {

			if ( empty( $processor['config']['entity_table'] ) ) return $ids;

			switch ( $processor['config']['entity_table'] ) {
				case 'civicrm_participant':
					$participant = $this->get_processor_from_magic(
						$processor['config']['entity_params'],
						$form,
						true
					);
					$ids[$participant['ID']] = $participant['config']['id'];
					break;

				case 'civicrm_membership':
					$membership = $this->get_processor_from_magic(
						$processor['config']['entity_params'],
						$form,
						true
					);
					$ids[$membership['ID']] = $membership['config']['membership_type_id'];
					break;

				case 'civicrm_contribution':
					$order = current(
						$this->get_processor_by_type( 'civicrm_order', $form )
					);

					if ( empty( $order ) || empty( $order['config']['contribution_page_id'] ) ) return $ids;

					$ids[$order['ID']] = $order['config']['contribution_page_id'];
					break;
			}

			return $ids;

		}, [] );

	}

	/**
	 * Parse processor id string containing '#'.
	 *
	 * @since 1.0.5
	 * @param string $processor_id The processor id
	 * @return string $processor_id The processor id
	 */
	public function parse_processor_id( $processor_id ) {
		return strpos( $processor_id, '#' ) ? substr( $processor_id, 0, strpos( $processor_id, '#' ) ) : $processor_id;
	}

	/**
	 * Retrieves a list of all field ids that
	 * are used for mapping to CiviCRM fields.
	 *
	 * @since 1.0.5
	 * @param array $form The form config
	 * @return array $mapped_fields_ids The mapped fields ids array
	 */
	public function get_all_mapped_fields_ids( $form ) {

		if ( empty( $form['processors'] ) ) return [];

		$civicrm_processors_config = array_reduce(
			$form['processors'],
			function ( $list, $processor ) {
				if ( false === strpos( $processor['type'], 'civicrm_' ) ) return $list;
				$list[$processor['ID']] = $processor['config'];
				return $list;
			},
			[]
		);

		// create recuresive iterator with all processors fields
		$processors_fields = new RecursiveIteratorIterator(
			new RecursiveArrayIterator( $civicrm_processors_config )
		);
		// filter out empty fields
		$processors_fields_array = array_filter( iterator_to_array( $processors_fields ) );

		// array with field ids
		return array_reduce(
			$processors_fields_array,
			function( $list, $field_id ) use ( $form ) {

				if ( false !== strpos( $field_id, '%' ) ) {

					$list[] = Caldera_Forms_Field_Util::get_field_by_slug(
						str_replace('%', '', $field_id),
						$form
					)['ID'];

				} elseif ( false !== strpos( $field_id, 'fld_' ) ) {

					$list[] = $field_id;

				}

				return $list;

			},
			[]
		);

	}

}
