<?php

/**
 * CiviCRM Caldera Forms AJAX Class
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_AJAX {

    /**
     * Initialises this object.
     *
     * @since 0.4
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register hooks.
     *
     * @since 0.4.2
     */
    public function register_hooks() {

        // reorder processors on save form
        add_action( 'wp_ajax_civicrm_get_contacts', array( $this, 'get_civicrm_contacts' ) );

    }

    /**
	 * Get CiviCRM Contacts.
	 *
	 * @uses 'wp_ajax' filter
	 * @since 0.4.2
	 */
	public function get_civicrm_contacts() {
	    if ( isset( $_POST['search'] ) ) $search_term = $_POST['search'];
	 	if ( isset( $_POST['contact_id'] ) ) $contact_id = $_POST['contact_id'];

	 	if ( ! wp_verify_nonce( $_POST['nonce'], 'admin_get_civi_contact' ) ) return;

		$result = array();

		if ( isset( $contact_id ) ){

			$contact = civicrm_api3('Contact', 'getsingle', array(
				'sequential' => 1,
				'id' => $contact_id,
				'return' => array( 'sort_name', 'email' ),
			));
			$result[] = array( 'id' => $contact['id'], 'sort_name' => $contact['sort_name'] . ' :: ' . $contact['email'] );

		} elseif ( isset( $search_term ) ) {

			$contacts = civicrm_api3('Contact', 'get', array(
				'sequential' => 1,
				'sort_name' => $search_term,
				'return' => array( 'sort_name', 'email' ),
				'contact_type' => 'Individual',
				'is_deleted' => 0,
				'is_deceased' => 0,
			));

			foreach ( $contacts['values'] as $key => $contact ) {
				$result[] = array( 'id' => $contact['id'], 'sort_name' => $contact['sort_name'] . ' :: ' . $contact['email'] );
			}

		}

		echo json_encode( $result );
		die;
	}
}
