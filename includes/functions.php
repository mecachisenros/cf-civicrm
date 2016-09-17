<?php

/**
 * Hook when form is loaded and before rendering.
 *
 * Validates checksum and fills in the form with Contact data.
 *
 * @since 0.1
 *
 * @uses 'caldera_forms_render_get_form' filter
 *
 * @param array $form The existing form array
 * @return array $form The modified form array
 */
function cf_pre_render_civicrm_form( $form ) {

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

		switch ( $pr_id['type'] ) {

			// Contact Processor
			case 'civicrm_contact':

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

				break;

			// Address Processor
			case 'civicrm_address':

				if ( isset( $civi_transdata['contact_id'] ) ) {
					try {

						$civi_contact_address = civicrm_api3( 'Address', 'getsingle', array(
							'sequential' => 1,
							'contact_id' => $civi_transdata['contact_id_' . $pr_id['config']['contact_link']],
							'location_type_id' => $pr_id['config']['location_type_id'],
						));

					} catch ( Exception $e ) {
						// Ignore if we have more than one address with same location type
					}
				}

				unset( $pr_id['config']['contact_link'] );

				if ( isset( $civi_contact_address ) && ! isset( $civi_contact_address['count'] ) ) {
					foreach ( $pr_id['config'] as $field => $value ) {
						if ( ! empty( $value ) ) {
							$form['fields'][$value]['config']['default'] = $civi_contact_address[$field];
						}
					}
				}

				// Clear Address data
				unset( $civi_contact_address );

				break;

			// Email Processor
			case 'civicrm_email':

				if ( isset( $civi_transdata['contact_id'] ) ) {
					try {

						$civi_contact_email = civicrm_api3( 'Email', 'getsingle', array(
							'sequential' => 1,
							'contact_id' => $civi_transdata['contact_id_' . $pr_id['config']['contact_link']],
							'location_type_id' => $pr_id['config']['location_type_id'],
						));

					} catch ( Exception $e ) {
						// Ignore if we have more than one email with same location type or none
					}
				}

				unset( $pr_id['config']['contact_link'] );

				if ( isset( $civi_contact_email ) && ! isset( $civi_contact_email['count'] ) ) {
					foreach ( $pr_id['config'] as $field => $value ) {
						if ( ! empty( $value ) ) {
							$form['fields'][$value]['config']['default'] = $civi_contact_email[$field];
						}
					}
				}

				// Clear Address data
				unset( $civi_contact_email );

				break;

			// Phone Processor
			case 'civicrm_phone':

				if ( isset( $civi_transdata['contact_id'] ) ) {
					try {

						$civi_contact_phone = civicrm_api3( 'Phone', 'getsingle', array(
							'sequential' => 1,
							'contact_id' => $civi_transdata['contact_id_' . $pr_id['config']['contact_link']],
							'location_type_id' => $pr_id['config']['location_type_id'],
						));

					} catch ( Exception $e ) {
						// Ignore if we have more than one phone with same location type or none
					}
				}

				unset( $pr_id['config']['contact_link'] );

				if ( isset( $civi_contact_phone ) && ! isset( $civi_contact_phone['count'] ) ) {
					foreach ( $pr_id['config'] as $field => $value ) {
						if ( ! empty( $value ) ) {
							$form['fields'][$value]['config']['default'] = $civi_contact_phone[$field];
						}
					}
				}

				// Clear Address data
				unset( $civi_contact_phone );

				break;
		}
	}

	return $form;

}

// add filter for the above function
add_filter( 'caldera_forms_render_get_form', 'cf_pre_render_civicrm_form' );

