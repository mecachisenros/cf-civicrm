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
		foreach ( $config as $key => $field_id ) {
			$form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
		}

		// Set Contact type and sub-type from prcessor config
		$form_values['contact_type'] = $config['contact_type'];
		$form_values['contact_sub_type'] = $config['contact_sub_type'];

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
		$dedupeParams = CRM_Dedupe_Finder::formatParams( $form_values, $config['contact_type'] );
		$dedupeParams['check_permission'] = FALSE;

		// Check dupes
		$ids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams, $config['contact_type'], NULL, array(), $config['dedupe_rule'] );

		// Pass contact id if found
		$form_values['contact_id'] = $ids ? $ids[0] : 0;

		// Unset 'group', for some reason Civi's Api errors if present
		// unset( $form_values['group'] );
		$create_contact = civicrm_api3( 'Contact', 'create', $form_values );

		// Set returned contact_id to $transdata for later use
		// $transdata['civicrm']['contact_id'] = $create_contact['id'];

		// Store $cid
		CiviCRM_Caldera_Forms_Helper::set_civi_transdata( $config['contact_link'], $create_contact['id'] );
		$transdata['civicrm'] = CiviCRM_Caldera_Forms_Helper::get_civi_transdata();

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

				// Map CiviCRM contact data to form defaults
				if ( isset( $civi_contact ) && $civi_contact != 0 ) {
					CiviCRM_Caldera_Forms_Helper::set_civi_transdata( $pr_id['config']['contact_link'], $civi_contact['contact_id'] );
					$civi_transdata = CiviCRM_Caldera_Forms_Helper::get_civi_transdata();

					unset( $pr_id['config']['auto_pop'], $pr_id['config']['contact_type'], $pr_id['config']['contact_sub_type'], $pr_id['config']['contact_link'], $pr_id['config']['dedupe_rule'] );

					foreach ( $pr_id['config'] as $field => $value ) {
						if ( ! empty( $value ) ) {
							$form['fields'][$value]['config']['default'] = $civi_contact[$field];
						}
					}
				}

				// Clear Contact data
				unset( $civi_contact );

			}
		}

		return $form;
	}

}
