<?php

/**
 * CiviCRM Caldera Forms Core Fields Presets Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Core_Fields_Presets {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

    /**
     * List of core fields.
     *
     * @since 0.4.4
     * @var $core_fields
     */
    public $core_fields = [
    	'prefix_id' => 'prefix_id',
    	'suffix_id' => 'suffix_id',
    	'gender_id' => 'gender_id',
    	'communication_style_id' => 'communication_style_id',
    	'do_not_email' => 'do_not_email',
    	'do_not_sms' => 'do_not_sms',
    	'do_not_trade' => 'do_not_trade',
    	'is_opt_out' => 'is_opt_out',
    	'country_id' => 'country_id',
    	'state_province_id' => 'state_province_id,',
    	'location_type_id' => 'address_location_type_id',
    	'e_location_type_id' => 'email_location_type_id',
    	'p_location_type_id' => 'phone_location_type_id',
    	'phone_type_id' => 'phone_type_id',
    	'website_type_id' => 'website_type_id',
    	'provider_id' => 'provider_id',
    	'preferred_language' => 'preferred_language'
    ];

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

		// auto-populate CiviCRM values
		add_action( 'caldera_forms_autopopulate_types', [ $this, 'autopulate_core_fields_types' ] );
		add_filter( 'caldera_forms_render_get_field', [ $this, 'autopopulate_core_fields_values' ], 20, 2 );
		// payment instruments presets
		add_filter( 'caldera_forms_field_option_presets', [ $this, 'payment_instrument_options_presets' ] );

	}

	/**
	 * Adds Payment method options Presets.
	 *
	 * @uses 'caldera_forms_field_option_presets' filter
	 *
	 * @since 0.4.4
	 *
	 * @param array $presets The existing presets
	 * @return array $presets The modified presets
	 */
	public function payment_instrument_options_presets( $presets ) {
		$result = civicrm_api3( 'Contribution', 'getoptions', [
			'field' => 'payment_instrument_id',
		] );
		
		$options = [];
		foreach ( $result['values'] as $id => $method ) {
			$options[] = $id.'|'.$method;
		}

		$presets['payment_instrument_id'] = [
			'name' => __( 'CiviCRM Payment Methods', 'caldera-forms-civicrm' ),
			'data' => $options,
		];

		return $presets;
	}

	/**
	 * Adds CiviCRM fields options to CF Autopopulate field type.
	 *
	 * @uses 'caldera_forms_autopopulate_types' action
	 *
	 * @since 0.2
	 */
	public function autopulate_core_fields_types() {

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
		// Website Type
		echo "<option value=\"website_type_id\"{{#is auto_type value=\"website_type_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Website Type', 'caldera-forms-civicrm' ) . "</option>";
		// IM Type
		echo "<option value=\"provider_id\"{{#is auto_type value=\"provider_id\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Im Type', 'caldera-forms-civicrm' ) . "</option>";
		// Preferred Language
		echo "<option value=\"preferred_language\"{{#is auto_type value=\"preferred_language\"}} selected=\"selected\"{{/is}}>" . __( 'CiviCRM - Preferred Language', 'caldera-forms-civicrm' ) . "</option>";

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
	public function autopopulate_core_fields_values( $field, $form ) {

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
					$state_province_id = $this->plugin->helper->get_state_province();
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

				// Website Type
				case 'website_type_id':
					$website_type_id = civicrm_api3( 'Website', 'getoptions', array(
						'sequential' => 1,
						'field' => 'website_type_id',
					));
					foreach ( $website_type_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;
				// IM Type
				case 'provider_id':
					$provider_id = civicrm_api3( 'Im', 'getoptions', array(
						'sequential' => 1,
						'field' => 'provider_id',
					));
					foreach ( $provider_id['values'] as $index ) {
							$field['config']['option'][$index['key']] = array(
								'value' => $index['key'],
								'label' => $index['value']
							);
					}
					break;
				// Preferred Language
				case 'preferred_language':
					$preferred_language = CRM_Contact_BAO_Contact::buildOptions('preferred_language');
					foreach ( $preferred_language as $key => $value ) {
							$field['config']['option'][$key] = array(
								'value' => $key,
								'label' => $value
							);
					}
					break;
			}

		}

		return $field;

	}

}

