<?php

/**
 * CiviCRM Caldera Forms Helper Class.
 *
 * @since 0.1
 */
class CiviCRM_Caldera_Forms_Helper {

	/**
	 * Contact fields.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $contact_fields The contact fields
	 */
	public static $contact_fields = array( 'prefix_id', 'first_name', 'last_name', 'middle_name', 'suffix_id', 'is_opt_out', 'nick_name', 'source', 'formal_title', 'job_title', 'gender_id', 'birth_date', 'email', 'current_employer', 'do_not_phone', 'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_trade', 'legal_identifier', 'legal_name', 'preferred_communication_method', 'preferred_language', 'preferred_mail_format', 'communication_style_id', 'household_name', 'organization_name', 'sic_code' );

	/**
	 * Activity fields.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $activity_fields The activity fields
	 */
	public static $activity_fields = array( 'activity_type_id', 'phone_id', 'phone_number', 'status_id', 'priority_id', 'parent_id', 'is_test', 'medium_id', 'is_auto', 'is_current_revision', 'result', 'is_deleted', 'campaign_id', 'engagement_level', 'weight', 'id', 'original_id', 'relationship_id');

	/**
	 * Holds CiviCRM state/province data which only needs a single lookup.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $states The CiviCRM state/province data
	 */
	public static $states;

	/**
	 * Holds contact ids for linking processors.
	 *
	 * @since 0.1
	 * @access public
	 * @var aray $civi_transdata The contact ids for linking processors
	 */
	public static $civi_transdata = array();

	/**
	 * Sets the contact_id/contact_id mapping.
	 *
	 * @since 0.1
	 *
	 * @param int $contact_link The Contact link from processor $config
	 * @param int $cid The Contact ID
	 */
	public static function set_civi_transdata( $contact_link, $cid ) {
		self::$civi_transdata['contact_id_' . $contact_link] = $cid;
	}

	/**
	 * Returns all contact_link/contact_id mappings.
	 *
	 * @since 0.1
	 *
	 * @return array $civi_transdata The contact_link/contact_id mapping array
	 */
	public static function get_civi_transdata() {
		return self::$civi_transdata;
	}

	/**
	 * Retrieve the select dropdown for contact links.
	 *
	 * @since 0.1
	 *
	 * @return str $contact_link The the select dropdown markup
	 */
	public static function contact_link_field() {

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
	public static function get_contact_custom_fields() {

		$contact_types = civicrm_api3( 'ContactType', 'get', array(
			'sequential' => 1,
		));

		// Include Contact entity by default
		$types = array( 'Contact' );
		foreach ( $contact_types['values'] as $key => $value ) {
			$types[] = $value['name'];
		}

		$extends = array( 'IN' => $types );

		$custom_group = civicrm_api3( 'CustomGroup', 'get', array(
			'sequential' => 1,
			'extends' => apply_filters( 'civicrm_custom_fields_contact_type', $extends ),
			'api.CustomField.get' => array( 'is_active' => 1, 'options' => array( 'limit' => 0 ) ),
			'options' => array( 'limit' => 0 ),
		));

		$custom_fields = array();
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
	public static function get_wp_civi_contact( $id ) {

		$params = array(
			'sequential' => 1,
			'uf_id' => $id,
			'domain_id' => CRM_Core_BAO_Domain::getDomain()->id,
		);

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
	public static function get_civi_contact( $cid ) {

		if ( $cid != 0 ) {

			$fields = civicrm_api3( 'Contact', 'getsingle', array(
				'sequential' => 1,
				'id' => $cid,
			 ));

			// Custom fields
			$c_fields = CiviCRM_Caldera_Forms_Helper::get_contact_custom_fields();

			$c_fields_string = '';
			foreach ( $c_fields as $key => $value ) {
				$c_fields_string .= $key . ',';
			}

			$custom_fields = civicrm_api3( 'Contact', 'getsingle', array(
				'sequential' => 1,
				'id' => $cid,
				'return' => $c_fields_string,
			 ));

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
	public static function get_all_fields() {

		$contact_fields = civicrm_api3( 'Contact', 'getfields', array(
			'sequential' => 1,
		));

		return $contact_fields['values'];

	}

	/**
	 * Get Countries from CiviCRM.
	 *
	 * @since 0.2
	 *
	 * @return array $states The array of countries
	 */
	public static function get_countries() {

		// define basic API vars
		$api_vars = array(
			'sequential' => 1,
			'options' => array( 'limit' => 0 ),
		);

		// get the countries enabled in CiviCRM localization settings
		$countries_enabled = CiviCRM_Caldera_Forms_Helper::get_civicrm_settings( 'countryLimit' );

		// limit to these if there are some defined
		if ( ! empty( $countries_enabled ) ) {
			$api_vars['id'] = array( 'IN' => $countries_enabled );
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
	public static function get_state_province() {

		// send data back if already retrieved
		if ( isset( self::$states ) ) return self::$states;

		$query = 'SELECT name,id,country_id FROM civicrm_state_province';
		$dao = CRM_Core_DAO::executeQuery( $query );
		self::$states = array();

		while ( $dao->fetch() ) {
			self::$states[$dao->id] = array( 'name' => $dao->name, 'country_id' => $dao->country_id );
		}

		foreach ( self::$states as $state_id => $state ) {
			if ( ! in_array( $state['country_id'], self::get_civicrm_settings( 'countryLimit' ) ) ) {
				unset( self::$states[$state_id] );
			}
		}

		return self::$states;

	}

	/**
	 * Get CiviCRM settings.
	 *
	 * @since 0.1
	 *
	 * @param str $setting The name of the setting to be returned
	 * @return array $settings The requested settings
	 */
	public static function get_civicrm_settings( $setting ){

		$settings = civicrm_api3( 'Setting', 'getvalue', array(
			'sequential' => 1,
			'name' => $setting,
		));

		return $settings;

	}

	/**
	 * Get Deduplicate rules.
	 *
	 * @since 0.1
	 *
	 * @return array $dedupe_rules The deduplicate rules
	 */
	public static function get_dedupe_rules() {

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
	public static function custom_field_extends( $custom_id ) {

		$custom_id = str_replace( 'custom_', '', $custom_id );
		$id = (int)$custom_id;

		$result = civicrm_api3( 'CustomField', 'getsingle', array(
			'sequential' => 1,
			'id' => $id,
			'api.CustomGroup.getsingle' => array(
				'id' => '$value.custom_group_id',
				'return' => array( 'extends_entity_column_value', 'extends' )
			),
		));

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
	public static function map_fields_to_processor( $config, $form, &$form_values, $processor = null ){

		foreach ( ( $processor ? $config[$processor] : $config ) as $key => $field_id ) {
			if ( ! empty( $field_id ) ) {

				// If we have a bracket magic tag, do bracket magic tag
				if ( strpos( $field_id, '{' ) !== false ) {
					$mapped_field = Caldera_Forms_Magic_Doer::do_bracket_magic( $field_id, $form, NULL, NULL, NULL );
				} else {

					// Get field by ID or slug
					$mapped_field =
						Caldera_Forms_Field_Util::get_field( $field_id, $form ) ?
						Caldera_Forms_Field_Util::get_field( $field_id, $form ) :
						Caldera_Forms::get_field_by_slug(str_replace( '%', '', $field_id ), $form );

					// Get field data
					$mapped_field = Caldera_Forms::get_field_data( $mapped_field['ID'], $form );
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
	public static function map_fields_to_prerender( $config, &$form, $ignore_fields, $entity, $processor = null ){

		foreach ( ( $processor ? $config[$processor] : $config ) as $field => $value ) {
			if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {

				// Get field by ID or slug
				$mapped_field =
					Caldera_Forms_Field_Util::get_field( $value, $form ) ?
					Caldera_Forms_Field_Util::get_field( $value, $form ) :
					Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );

				// Set default value
				$form['fields'][$mapped_field['ID']]['config']['default'] = $entity[$field];
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
	public static function get_enabled_extensions(){
		try {
			$result = civicrm_api3( 'Extension', 'get', array(
				'sequential' => 1,
  				'is_active' => 1,

			));
		} catch ( Exception $e ) {

		}

		$enabled_extensions = array();
		if( $result['is_error'] == 0 ){
			foreach ( $result['values'] as $key => $extension) {
				$enabled_extensions[] = $extension['key'];
			}
			return $enabled_extensions;
		}

		return false;
	}

}
