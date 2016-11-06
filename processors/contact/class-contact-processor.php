<?php

/**
 * CiviCRM Caldera Forms Contact Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Contact_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_contact';

	/**
	 * Entities that are allowed to pre-render CiviCRM data, excluding the Contact itself ie. 'civicrm_contact'.
	 *
	 * @since 0.3
	 * @access private
	 * @var array $entities_to_prerender
	 */
	private $entities_to_prerender = array( 'process_address', 'process_phone', 'process_email', 'process_website', 'process_im' );

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );
		// filter form before rendering
		add_filter( 'caldera_forms_render_get_form', array( $this, 'pre_render') );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.2
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = array(
			'name' =>  __( 'CiviCRM Contact', 'caldera-forms-civicrm' ),
			'description' =>  __( 'Create CiviCRM contact', 'caldera-forms-civicrm' ),
			'author' =>  'Andrei Mondoc',
			'template' =>  CF_CIVICRM_INTEGRATION_PATH . 'processors/contact/contact_config.php',
			'processor' =>  array( $this, 'processor' ),
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		// Get form values for each processor field
		// $value is the field id
		$form_values = array();
		foreach ( $config['civicrm_contact'] as $key => $field_id ) {
			$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
			if( ! empty( $mapped_field ) ){
				$form_values['civicrm_contact'][$key] = $mapped_field;
			}
		}

		if ( ! empty( $form_values['civicrm_contact'] ) ) {

			// Set Contact type and sub-type from prcessor config
			$form_values['civicrm_contact']['contact_type'] = $config['civicrm_contact']['contact_type'];
			$form_values['civicrm_contact']['contact_sub_type'] = $config['civicrm_contact']['contact_sub_type'];

			// Use 'Process email' field for deduping if enabled
			if ( isset( $config['email_enabled'] ) ) {
				foreach ( $config['civicrm_email'] as $key => $field_id ) {
					if ( $key === 'email' ) {
						$form_values['civicrm_contact'][$key] = Caldera_Forms::get_field_data( $field_id, $form );
					}
				}
			}

			// Indexed array containing the Email processors
			$civicrm_email_pr = Caldera_Forms::get_processor_by_type( 'civicrm_email', $form );
			if ( $civicrm_email_pr ) {
				foreach ( $civicrm_email_pr as $key => $value ) {
					if ( ! is_int( $key ) ) {
						unset( $civicrm_email_pr[$key] );
					}
				}
			}

			// FIXME Add Email processor option to set Defaul email for deduping?
			// Override Contact processor email address with first Email processor
			if ( $civicrm_email_pr ) {
				foreach ( $civicrm_email_pr[0]['config'] as $field => $value ) {
					if ( $field === 'email' ) {
						$form_values[$field] = $transdata['data'][$value];
					}
				}
			}

			// Dupes params
			$dedupeParams = CRM_Dedupe_Finder::formatParams( $form_values['civicrm_contact'], $config['civicrm_contact']['contact_type'] );
			$dedupeParams['check_permission'] = FALSE;

			// Check dupes
			$ids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams, $config['civicrm_contact']['contact_type'], NULL, array(), $config['civicrm_contact']['dedupe_rule'] );

			// Pass contact id if found
			$form_values['civicrm_contact']['contact_id'] = $ids ? $ids[0] : 0;

			$create_contact = civicrm_api3( 'Contact', 'create', $form_values['civicrm_contact'] );

			// Store $cid
			CiviCRM_Caldera_Forms_Helper::set_civi_transdata( $config['contact_link'], $create_contact['id'] );
			$transdata['civicrm'] = CiviCRM_Caldera_Forms_Helper::get_civi_transdata();

			/**
			 * Process enabled entities.
			 * @since 0.3
			 */
			if ( isset( $config['enabled_entities'] ) ){
				foreach ( $config['enabled_entities'] as $entity => $value ) {
					if( isset( $entity ) && $value == 1 ){
						$this->$entity( $config, $form, $transdata, $form_values );
					}
				}
			}

		}

	}

	/**
	 * Process Address.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_address( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {
				$address = civicrm_api3( 'Address', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['civicrm_address']['location_type_id'],
				));
			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			foreach ( $config['civicrm_address'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values['civicrm_address'][$key] = $mapped_field;
				}
			}

			if( ! empty( $form_values['civicrm_address'] ) ) {
				$form_values['civicrm_address']['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass address ID if we got one
				if ( isset( $address ) && is_array( $address ) ) {
					$form_values['civicrm_address']['id'] = $address['id']; // Address ID
				} else {
					$form_values['civicrm_address']['location_type_id'] = $config['civicrm_address']['location_type_id'];
				}

				// FIXME
				// Concatenate DATE + TIME
				// $form_values['activity_date_time'] = $form_values['activity_date_time'];

				$create_address = civicrm_api3( 'Address', 'create', $form_values['civicrm_address'] );
			}
		}
	}

	/**
	 * Process Phone.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_phone( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$phone = civicrm_api3( 'Phone', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['civicrm_phone']['location_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			foreach ( $config['civicrm_phone'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values['civicrm_phone'][$key] = $mapped_field;
				}
			}

			if( ! empty( $form_values['civicrm_phone'] ) ) {
				$form_values['civicrm_phone']['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass Phone ID if we got one
				if ( isset( $phone ) && is_array( $phone ) ) {
					$form_values['civicrm_phone']['id'] = $phone['id']; // Phone ID
				} else {
					$form_values['civicrm_phone']['location_type_id'] = $config['civicrm_phone']['location_type_id'];
				}

				$create_phone = civicrm_api3( 'Phone', 'create', $form_values['civicrm_phone'] );
			}
		}
	}

	/**
	 * Process Note.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_note( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			foreach ( $config['civicrm_note'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values['civicrm_note'][$key] = $mapped_field;
				}
			}

			if( ! empty( $form_values['civicrm_note'] ) ) {
				$form_values['civicrm_note']['entity_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Add Note to contact
				$note = civicrm_api3( 'Note', 'create', $form_values['civicrm_note'] );
			}
		}
	}

	/**
	 * Process Email.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_email( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$email = civicrm_api3( 'Email', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['civicrm_email']['location_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			foreach ( $config['civicrm_email'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values['civicrm_email'][$key] = $mapped_field;
				}
			}

			if ( ! empty( $form_values['civicrm_email'] ) ) {
				$form_values['civicrm_email']['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass Email ID if we got one
				if ( isset( $email ) && is_array( $email ) ) {
					$form_values['civicrm_email']['id'] = $email['id']; // Email ID
				} else {
					$form_values['civicrm_email']['location_type_id'] = $config['civicrm_email']['location_type_id'];
				}

				$create_email = civicrm_api3( 'Email', 'create', $form_values['civicrm_email'] );
			}
		}
	}

	/**
	 * Process Website.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_website( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$website = civicrm_api3( 'Website', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'website_type_id' => $config['civicrm_website']['website_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			foreach ( $config['civicrm_website'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if( ! empty( $mapped_field ) ){
					$form_values['civicrm_website'][$key] = $mapped_field;
				}
			}

			if( ! empty( $form_values['civicrm_website'] ) ) {
				$form_values['civicrm_website']['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass Website ID if we got one
				if ( isset( $website ) && is_array( $website ) ) {
					$form_values['civicrm_website']['id'] = $website['id']; // Website ID
				} else {
	                $form_values['civicrm_website']['website_type_id'] = $config['civicrm_website']['website_type_id'];
	            }

				$create_email = civicrm_api3( 'Website', 'create', $form_values['civicrm_website'] );
			}
		}
	}

	/**
	 * Process Im.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_im( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {

			try {

				$im = civicrm_api3( 'Im', 'getsingle', array(
					'sequential' => 1,
					'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
					'location_type_id' => $config['civicrm_im']['location_type_id'],
				));

			} catch ( Exception $e ) {
				// Ignore if none found
			}

			// Get form values for each processor field
			// $value is the field id
			foreach ( $config['civicrm_im'] as $key => $field_id ) {
				$mapped_field = Caldera_Forms::get_field_data( $field_id, $form );
				if ( ! empty( $mapped_field ) ){
					$form_values['civicrm_im'][$key] = $mapped_field;
				}
			}
			if( ! empty( $form_values['civicrm_im'] ) ){
				$form_values['civicrm_im']['contact_id'] = $transdata['civicrm']['contact_id_' . $config['contact_link']]; // Contact ID set in Contact Processor

				// Pass Im ID if we got one
				if ( isset( $im ) && is_array( $im ) ) {
					$form_values['civicrm_im']['id'] = $im['id']; // Im ID
				} else {
	                $form_values['civicrm_im']['location_type_id'] = $config['civicrm_im']['location_type_id']; // IM Location type set in Processor config
	            }

				$create_im = civicrm_api3( 'Im', 'create', $form_values['civicrm_im'] );
			}
		}
	}

	/**
	 * Process Group.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_group( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {
			$result = civicrm_api3( 'GroupContact', 'create', array(
				'sequential' => 1,
				'group_id' => $config['civicrm_group']['contact_group'], // Group ID from processor config
				'contact_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']], // Contact ID set in Contact Processor
			));
		}

	}

	/**
	 * Process Tag.
	 *
	 * @since 0.3
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param array $transdata The globalised transient object
	 * @param array $form_values The field values beeing submitted
	 */
	public function process_tag( $config, $form, $transdata, $form_values ){

		if ( ! empty( $transdata['civicrm']['contact_id_' . $config['contact_link']] ) ) {
			foreach ( $config['civicrm_tag'] as $key => $value ) {
				if ( stristr( $key, 'entity_tag' ) != false ) {
					$tag = civicrm_api3( 'Tag', 'getsingle', array(
						'sequential' => 1,
						'id' => $value,
						'api.EntityTag.create' => array(
							'entity_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
							'entity_table' => 'civicrm_contact',
							'tag_id' => '$value.id',
						),
					));
				}
			}
		}

	}

	/**
	 * Autopopulates Form with Civi data
	 *
	 * @uses 'caldera_forms_render_get_form' filter
	 *
	 * @since 0.2
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function pre_render( $form ){

		// globalised transient object
		global $transdata;

		// Indexed array containing the Contact processors
		$civicrm_contact_pr = Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form );
		if ( $civicrm_contact_pr ) {
			foreach ( $civicrm_contact_pr as $key => $value ) {
				if ( ! is_int( $key ) ) {
					unset( $civicrm_contact_pr[$key] );
				}
			}
		}

		foreach ( $form['processors'] as $processor => $pr_id ) {
			if( $pr_id['type'] == $this->key_name ){

				if ( isset( $pr_id['config']['auto_pop'] ) && $pr_id['config']['auto_pop'] == 1 && $civicrm_contact_pr[0]['ID'] == $pr_id['ID'] ) {

					// Get contact_id if user is logged in
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$current_user = CiviCRM_Caldera_Forms_Helper::get_wp_civi_contact( $current_user->ID );

						$civi_contact = CiviCRM_Caldera_Forms_Helper::get_civi_contact( $current_user );

					} else {
						$civi_contact = 0;
					}

				}

				// FIXME
				// Just for testing, remove later
				// if ( isset( $_GET['cid'] ) && $civicrm_contact_pr[0]['ID'] == $pr_id['ID'] ) {
				//	 $cid = $_GET['cid'];
				//	 $civi_contact = CiviCRM_Caldera_Forms_Helper::get_civi_contact( $cid );
				// }

				// Get request cid(contact_id) and cs(checksum)
				// FIXME
				// Checksum overrides Logged in, is this what we want?
				if ( isset( $_GET['cid'] ) && isset( $_GET['cs'] ) && $civicrm_contact_pr[0]['ID'] == $pr_id['ID'] ) {

					$cid = $_GET['cid'];
					$cs = $_GET['cs'];

					// Check for valid checksum
					$valid_user = CRM_Contact_BAO_Contact_Utils::validChecksum( $cid, $cs );

					if ( $valid_user ) {
						$civi_contact = CiviCRM_Caldera_Forms_Helper::get_civi_contact( $cid );
					}

					// FIXME
					// Add permission check
					$permissions = CRM_Core_Permission::getPermission();

				}

				// Fields to ignore when populating/mapping Civi data to form fields
				$ignore_fields = array( 'auto_pop', 'contact_type', 'contact_sub_type', 'contact_link', 'dedupe_rule', 'location_type_id', 'website_type_id' );

				// Map CiviCRM contact data to form defaults
				if ( isset( $civi_contact ) && $civi_contact != 0 ) {
					CiviCRM_Caldera_Forms_Helper::set_civi_transdata( $pr_id['config']['contact_link'], $civi_contact['contact_id'] );
					$transdata['civicrm'] = CiviCRM_Caldera_Forms_Helper::get_civi_transdata();

					foreach ( $pr_id['config']['civicrm_contact'] as $field => $value ) {
						if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
							$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
							$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact[$field];
						}
					}
				}

				// Clear Contact data
				unset( $civi_contact );

				// Map CiviCRM data for the enabled entities/processors
				if ( isset( $pr_id['config']['enabled_entities'] ) ){
					foreach ( $pr_id['config']['enabled_entities'] as $entity => $value) {
						if( isset( $entity ) && in_array( $entity, $this->entities_to_prerender ) ){
							$pre_render_entity = str_replace( 'process_', 'pre_render_', $entity );
							$this->$pre_render_entity( $pr_id, $transdata, $form, $ignore_fields );
						}
					}
				}

			}
		}

		return $form;
	}

	/**
	 * Pre-render Address data.
	 *
	 * @since 0.3
	 *
	 * @param array $pr_id The processor
	 * @param array $transdata The globalised transient object
	 * @param array $form The Form object
	 * @param array $ignore_fields Fields to ignore when mapping Civi data into the form
	 */
	public function pre_render_address( $pr_id, $transdata, &$form, $ignore_fields ){

		if( isset( $pr_id['config']['enabled_entities']['process_address'] ) ){
			if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
				try {

					$civi_contact_address = civicrm_api3( 'Address', 'getsingle', array(
						'sequential' => 1,
						'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
						'location_type_id' => $pr_id['config']['civicrm_address']['location_type_id'],
					));

				} catch ( Exception $e ) {
					// Ignore if we have more than one address with same location type
				}
			}

			if ( isset( $civi_contact_address ) && ! isset( $civi_contact_address['count'] ) ) {
				foreach ( $pr_id['config']['civicrm_address'] as $field => $value ) {
					if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
						$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
						$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact_address[$field];
					}
				}
			}

			// Clear Address data
			unset( $civi_contact_address );

		}
	}

	/**
	 * Pre-render Phone data.
	 *
	 * @since 0.3
	 *
	 * @param array $pr_id The processor
	 * @param array $transdata The globalised transient object
	 * @param array $form The Form object
	 * @param array $ignore_fields Fields to ignore when mapping Civi data into the form
	 */
	public function pre_render_phone( $pr_id, $transdata, &$form, $ignore_fields ){

		if( isset( $pr_id['config']['enabled_entities']['process_phone'] ) ){
			if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
				try {

					$civi_contact_phone = civicrm_api3( 'Phone', 'getsingle', array(
						'sequential' => 1,
						'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
						'location_type_id' => $pr_id['config']['civicrm_phone']['location_type_id'],
					));

				} catch ( Exception $e ) {
					// Ignore if we have more than one phone with same location type or none
				}
			}

			if ( isset( $civi_contact_phone ) && ! isset( $civi_contact_phone['count'] ) ) {
				foreach ( $pr_id['config']['civicrm_phone'] as $field => $value ) {
					if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
						$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
						$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact_phone[$field];
					}
				}
			}

			// Clear Phone data
			unset( $civi_contact_phone );
		}
	}

	/**
	 * Pre-render Email data.
	 *
	 * @since 0.3
	 *
	 * @param array $pr_id The processor
	 * @param array $transdata The globalised transient object
	 * @param array $form The Form object
	 * @param array $ignore_fields Fields to ignore when mapping Civi data into the form
	 */
	public function pre_render_email( $pr_id, $transdata, &$form, $ignore_fields ){

		if( isset( $pr_id['config']['enabled_entities']['process_email'] ) ){
			if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
				try {

					$civi_contact_email = civicrm_api3( 'Email', 'getsingle', array(
						'sequential' => 1,
						'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
						'location_type_id' => $pr_id['config']['civicrm_email']['location_type_id'],
					));

				} catch ( Exception $e ) {
					// Ignore if we have more than one email with same location type or none
				}
			}

			if ( isset( $civi_contact_email ) && ! isset( $civi_contact_email['count'] ) ) {
				foreach ( $pr_id['config']['civicrm_email'] as $field => $value ) {
					if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
						$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
						$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact_email[$field];
					}
				}
			}

			// Clear Email data
			unset( $civi_contact_email );
		}
	}

	/**
	 * Pre-render Website data.
	 *
	 * @since 0.3
	 *
	 * @param array $pr_id The processor
	 * @param array $transdata The globalised transient object
	 * @param array $form The Form object
	 * @param array $ignore_fields Fields to ignore when mapping Civi data into the form
	 */
	public function pre_render_website( $pr_id, $transdata, &$form, $ignore_fields ){

		if( isset( $pr_id['config']['enabled_entities']['process_website'] ) ){
			if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
				try {

					$civi_contact_website = civicrm_api3( 'Website', 'getsingle', array(
						'sequential' => 1,
						'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
						'website_type_id' => $pr_id['config']['civicrm_website']['website_type_id'],
					));

				} catch ( Exception $e ) {
					// Ignore if we have more than one website with same location type or none
				}
			}

			if ( isset( $civi_contact_website ) && ! isset( $civi_contact_website['count'] ) ) {
				foreach ( $pr_id['config']['civicrm_website'] as $field => $value ) {
					if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
						$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
						$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact_website[$field];
					}
				}
			}

			// Clear Website data
			unset( $civi_contact_website );
		}
	}

	/**
	 * Pre-render Im data.
	 *
	 * @since 0.3
	 *
	 * @param array $pr_id The processor
	 * @param array $transdata The globalised transient object
	 * @param array $form The Form object
	 * @param array $ignore_fields Fields to ignore when mapping Civi data into the form
	 */
	public function pre_render_im( $pr_id, $transdata, &$form, $ignore_fields ){

		if( isset( $pr_id['config']['enabled_entities']['process_im'] ) ){
			if ( isset( $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']] ) ) {
				try {

					$civi_contact_im = civicrm_api3( 'Im', 'getsingle', array(
						'sequential' => 1,
						'contact_id' => $transdata['civicrm']['contact_id_' . $pr_id['config']['contact_link']],
						'location_type_id' => $pr_id['config']['civicrm_im']['location_type_id'],
					));

				} catch ( Exception $e ) {
					// Ignore if we have more than one Im with same location type or none
				}
			}

			if ( isset( $civi_contact_im ) && ! isset( $civi_contact_im['count'] ) ) {
				foreach ( $pr_id['config']['civicrm_im'] as $field => $value ) {
					if ( ! empty( $value ) && ! in_array( $field, $ignore_fields ) ) {
						$mapped_field = Caldera_Forms::get_field_by_slug(str_replace( '%', '', $value ), $form );
						$form['fields'][$mapped_field['ID']]['config']['default'] = $civi_contact_im[$field];
					}
				}
			}

			// Clear Im data
			unset( $civi_contact_im );
		}
	}

}
