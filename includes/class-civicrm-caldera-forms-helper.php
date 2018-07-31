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
	public $contact_fields = [ 'prefix_id', 'first_name', 'last_name', 'middle_name', 'suffix_id', 'is_opt_out', 'nick_name', 'source', 'formal_title', 'job_title', 'gender_id', 'birth_date', 'email', 'current_employer', 'do_not_phone', 'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_trade', 'legal_identifier', 'legal_name', 'preferred_communication_method', 'preferred_language', 'preferred_mail_format', 'communication_style_id', 'household_name', 'organization_name', 'sic_code' ];

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
	 * Holds CiviCRM state/province data which only needs a single lookup.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $states The CiviCRM state/province data
	 */
	public $states;

	/**
	 * Holds contact ids for linking processors.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $civi_transdata The contact ids for linking processors
	 */
	public $civi_transdata = [];

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Holds field/file ids for attachments.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var array $file_entity_transdata File entity ids
	 */
	public $file_entity_transdata = [];

	/**
	 * Sets the contact_id/contact_id mapping.
	 *
	 * @since 0.1
	 *
	 * @param int $contact_link The Contact link from processor $config
	 * @param int $cid The Contact ID
	 */
	public function set_civi_transdata( $contact_link, $cid ) {
		$this->civi_transdata['contact_id_' . $contact_link] = $cid;
	}

	/**
	 * Returns all contact_link/contact_id mappings.
	 *
	 * @since 0.1
	 *
	 * @return array $civi_transdata The contact_link/contact_id mapping array
	 */
	public function get_civi_transdata() {
		return $this->civi_transdata;
	}

	/**
	 * Sets the field/file ids.
	 *
	 * @since 0.4.2
	 *
	 * @param array $params
	 */
	public function set_file_entity_ids( $params ) {
		$this->file_entity_transdata = $params;
	}

	/**
	 * Returns the field/file ids.
	 *
	 * @since 0.4.2
	 *
	 * @return array $params
	 */
	public function get_file_entity_ids() {
		return $this->file_entity_transdata;
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
		?>
				<select class="block-input field-config" name="{{_name}}[contact_link]">
					<option value="1" {{#is contact_link value=1}}selected="selected"{{/is}}><?php _e( 'Contact 1', 'caldera-forms-civicrm' ); ?></option>
					<option value="2" {{#is contact_link value=2}}selected="selected"{{/is}}><?php _e( 'Contact 2', 'caldera-forms-civicrm' ); ?></option>
					<option value="3" {{#is contact_link value=3}}selected="selected"{{/is}}><?php _e( 'Contact 3', 'caldera-forms-civicrm' ); ?></option>
					<option value="4" {{#is contact_link value=4}}selected="selected"{{/is}}><?php _e( 'Contact 4', 'caldera-forms-civicrm' ); ?></option>
					<option value="5" {{#is contact_link value=5}}selected="selected"{{/is}}><?php _e( 'Contact 5', 'caldera-forms-civicrm' ); ?></option>
					<option value="6" {{#is contact_link value=6}}selected="selected"{{/is}}><?php _e( 'Contact 6', 'caldera-forms-civicrm' ); ?></option>
					<option value="7" {{#is contact_link value=7}}selected="selected"{{/is}}><?php _e( 'Contact 7', 'caldera-forms-civicrm' ); ?></option>
					<option value="8" {{#is contact_link value=8}}selected="selected"{{/is}}><?php _e( 'Contact 8', 'caldera-forms-civicrm' ); ?></option>
					<option value="9" {{#is contact_link value=9}}selected="selected"{{/is}}><?php _e( 'Contact 9', 'caldera-forms-civicrm' ); ?></option>
					<option value="10" {{#is contact_link value=10}}selected="selected"{{/is}}><?php _e( 'Contact 10', 'caldera-forms-civicrm' ); ?></option>
				</select>
		<?php
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
			'extends' => apply_filters( 'civicrm_custom_fields_contact_type', $extends ),
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

		$wp_civicrm_contact = civicrm_api3( 'UFMatch', 'getsingle', $params );
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

			$fields = civicrm_api3( 'Contact', 'getsingle', $params );

			// Custom fields
			$c_fields = implode( ',', array_keys( $this->plugin->helper->get_contact_custom_fields() ) );

			if ( empty( $c_fields ) ) return $fields;

			$params['return'] = $c_fields;
			$custom_fields = civicrm_api3( 'Contact', 'getsingle', $params );

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

		$result = civicrm_api3( 'CustomField', 'getsingle', [
			'sequential' => 1,
			'id' => $id,
			'api.CustomGroup.getsingle' => [
				'id' => '$value.custom_group_id',
				'return' => [ 'extends_entity_column_value', 'extends' ]
			],
		] );

		if( isset( $result['api.CustomGroup.getsingle']['extends_entity_column_value'] ) ) {
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
		foreach ( ( $processor ? $config[$processor] : $config ) as $key => $field_id ) {
			if ( ! empty( $field_id ) ) {

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
					$mapped_field = $mapped_fields[0];

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

					// handle current_employers field
					if ( $key == 'current_employer' && $field['type'] == 'civicrm_contact_reference' ) {
						$employer = civicrm_api3( 'Contact', 'get', [ 'contact_id' => $mapped_field, 'return' => 'organization_name' ] );
						$mapped_field = $employer['values'][$employer['id']]['organization_name'];
					}
				}


        		if( ! empty( $mapped_field ) ){

					if ( $processor ) {
						$form_values[$processor][$key] = $mapped_field;
					} else {
						$form_values[$key] = $mapped_field;
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
	 * @param string $processor The processor key, only necessary for the Contact processor class
	 * @return array $form The form settings
	 */
	public function map_fields_to_prerender( $config, &$form, $ignore_fields, $entity, $processor = null ){

		foreach ( ( $processor ? $config[$processor] : $config ) as $field => $value ) {
			if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {

				// Get field by ID or slug
				$mapped_field =
					Caldera_Forms_Field_Util::get_field( $value, $form ) ?
					Caldera_Forms_Field_Util::get_field( $value, $form ) :
					Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );

				// Set default value
				$form['fields'][$mapped_field['ID']]['config']['default'] = $entity[$field];

				// handle current employer, api returns name, no cid/employer_id
				if ( $field == 'current_employer' && $mapped_field['type'] == 'civicrm_contact_reference' ) {
					$employer = civicrm_api3( 'Contact', 'get', [ 'sort_name' => $entity[$field] ] );
					$form['fields'][$mapped_field['ID']]['config']['default'] = $employer['id'];
				}

				if ( $mapped_field['type'] == 'radio' ) {
					$options = Caldera_Forms_Field_Util::find_option_values( $mapped_field );
					$form['fields'][$mapped_field['ID']]['config']['default'] = array_search( $entity[$field], $options );
				}
			}
		}

		return $form;
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
				'options' => [ 'limit' => 0 ],
			] );
		} catch ( Exception $e ) {

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
	 * Retrieve the select dropdown for contact links.
	 *
	 * @since 0.4.4
	 *
	 * @return str $contact_link The the select dropdown markup
	 */
	public function contact_link_order_field( $id ) {

		ob_start();
		?>
				<select class="block-input field-config" name="{{_name}}[line_item][<?php echo $id; ?>][contact_link]">
					<option value="1" {{#is line_item/<?php echo $id; ?>/contact_link value=1}}selected="selected"{{/is}}><?php _e( 'Contact 1', 'caldera-forms-civicrm' ); ?></option>
					<option value="2" {{#is line_item/<?php echo $id; ?>/contact_link value=2}}selected="selected"{{/is}}><?php _e( 'Contact 2', 'caldera-forms-civicrm' ); ?></option>
					<option value="3" {{#is line_item/<?php echo $id; ?>/contact_link value=3}}selected="selected"{{/is}}><?php _e( 'Contact 3', 'caldera-forms-civicrm' ); ?></option>
					<option value="4" {{#is line_item/<?php echo $id; ?>/contact_link value=4}}selected="selected"{{/is}}><?php _e( 'Contact 4', 'caldera-forms-civicrm' ); ?></option>
					<option value="5" {{#is line_item/<?php echo $id; ?>/contact_link value=5}}selected="selected"{{/is}}><?php _e( 'Contact 5', 'caldera-forms-civicrm' ); ?></option>
					<option value="6" {{#is line_item/<?php echo $id; ?>/contact_link value=6}}selected="selected"{{/is}}><?php _e( 'Contact 6', 'caldera-forms-civicrm' ); ?></option>
					<option value="7" {{#is line_item/<?php echo $id; ?>/contact_link value=7}}selected="selected"{{/is}}><?php _e( 'Contact 7', 'caldera-forms-civicrm' ); ?></option>
					<option value="8" {{#is line_item/<?php echo $id; ?>/contact_link value=8}}selected="selected"{{/is}}><?php _e( 'Contact 8', 'caldera-forms-civicrm' ); ?></option>
					<option value="9" {{#is line_item/<?php echo $id; ?>/contact_link value=9}}selected="selected"{{/is}}><?php _e( 'Contact 9', 'caldera-forms-civicrm' ); ?></option>
					<option value="10" {{#is line_item/<?php echo $id; ?>/contact_link value=10}}selected="selected"{{/is}}><?php _e( 'Contact 10', 'caldera-forms-civicrm' ); ?></option>
				</select>
		<?php
		$contact_link = ob_get_contents();
		return $contact_link;

	}

	public function get_field_data_by_slug( $slug, $form ) {
		$slug = strpos( $slug, '%' ) !== false ? str_replace( '%', '', $slug ) : $slug;
		$field = Caldera_Forms::get_field_by_slug( $slug, $form );
		return Caldera_Forms::get_field_data( $field['ID'], $form );
	}

	/**
	 * Get event price sets.
	 *
	 * @since 0.4.4
	 * @return array $price_sets The active price sets with their corresponding price fields and price filed values
	 */
	public function get_price_sets( $id = false ) {

		if ( isset( $this->price_sets ) ) return $this->price_sets;

		$price_set_params = array(
			'sequential' => 1,
			'is_active' => 1,
			'is_reserved' => 0,
			'options' => array( 'limit' => 0 ),
			'api.PriceField.get' => array(
				'sequential' => 0,
				'price_set_id' => "\$value.id",
				'is_active' => 1,
				'options' => array( 'limit' => 0 ),
			),
		);

		if( $id ) $price_set_params['id'] = $id;

		try {
			$event_price_sets = civicrm_api3( 'PriceSet', 'get', $price_set_params );
		} catch ( CiviCRM_API3_Exception $e ) {
			return array( 'note' => $e->getMessage(), 'type' => 'error' );
		}

		try {
			$all_price_field_values = civicrm_api3( 'PriceFieldValue', 'get', array(
				'sequential' => 0,
				'is_active' => 1,
				'options' => array( 'limit' => 0 ),
			));
		} catch ( CiviCRM_API3_Exception $e ) {
			return array( 'note' => $e->getMessage(), 'type' => 'error' );
		}

		$price_field_values = array();
		foreach ( $all_price_field_values['values'] as $id => $price_field_value ) {
			$price_field_value['amount'] = number_format( $price_field_value['amount'], 2, '.', '' );
			$price_field_values[$id] = $price_field_value;
		}

		$price_sets = array();
		foreach ( $event_price_sets['values'] as $key => $price_set ) {
			$price_set['price_set_id'] = $price_set_id = $price_set['id'];
			$price_set['price_fields'] = $price_set['api.PriceField.get']['values'];
			foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
				foreach ( $price_field_values as $value_id => $price_field_value) {
					if ( $price_field_id == $price_field_value['price_field_id'] ) {
						$price_set['price_fields'][$price_field_id]['price_field_values'][$value_id] = $price_field_value;
					}
				}
			}
			unset( $price_set['id'], $price_set['api.PriceField.get'] );
			$price_sets[$price_set_id] = $price_set;
		}
		$this->price_sets = $price_sets;

		return $this->price_sets;
	}

	public function get_price_field_value( $id ) {
		$price_field_value = civicrm_api3( 'PriceFieldValue', 'getsingle', [
			'return' => [ 'id', 'price_field_id', 'label', 'amount', 'count', 'membership_type_id', 'membership_num_terms', 'financial_type_id' ],
			'id' => $id,
			'is_active' => 1,
		] );
		$price_field_value['amount'] = number_format( $price_field_value['amount'], 2, '.', '' );
		return $price_field_value;
	}

}
