<?php

/**
 * CiviCRM Caldera Forms AJAX Class
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_AJAX {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	* Initialises this object.
	*
	* @since 0.4
	*/
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->register_hooks();
	}

	/**
	* Register hooks.
	*
	* @since 0.4.2
	*/
	public function register_hooks() {

		add_action( 'wp_ajax_civicrm_get_contacts', [ $this, 'get_civicrm_contacts' ] );
		add_action( 'wp_ajax_civicrm_get_groups', [ $this, 'civicrm_get_groups' ] );
		add_action( 'wp_ajax_flush_price_set_cache', [ $this, 'flush_price_set_cache' ] );
		add_action( 'wp_ajax_civicrm_contact_reference_get', [ $this, 'civicrm_contact_reference_get' ] );
		add_action( 'wp_ajax_nopriv_civicrm_contact_reference_get', [ $this, 'civicrm_contact_reference_get' ] );
		// event code discount
		add_action( 'wp_ajax_do_code_cividiscount', [ $this, 'do_code_cividiscount' ] );
		add_action( 'wp_ajax_nopriv_do_code_cividiscount', [ $this, 'do_code_cividiscount' ] );
		// premiums
		add_action( 'wp_ajax_civicrm_get_premiums', [ $this, 'civicrm_get_premiums' ] );
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

			if ( isset( $contact_id ) )
				$params['contact_id'] = $contact_id;
			// sort name
			if ( isset( $search_term ) )
				$params['sort_name'] = $search_term;

			$result = $this->get_contacts( $params, $with_email = true );

		echo json_encode( $result );
		die;
	}

	public function civicrm_contact_reference_get() {
		if ( isset( $_POST['search'] ) ) $search_term = $_POST['search'];
		if ( isset( $_POST['contact_id'] ) ) $contact_id = $_POST['contact_id'];
		if ( isset( $_POST['field_id'] ) ) $field_id = $_POST['field_id'];
		if ( isset( $_POST['form_id'] ) ) $form_id = $_POST['form_id'];
		if ( ! wp_verify_nonce( $_POST['nonce'], 'civicrm_contact_reference_get' ) ) return;

		// form config
		$form = Caldera_Forms::get_form( $form_id );
		// field config
		$field = Caldera_Forms_Field_Util::get_field( $field_id, $form );

		// contact_type
		if ( isset( $field['config']['contact_type'] ) )
			$params['contact_type'] = $field['config']['contact_type'];
		// contact_sub_type
		if ( isset( $field['config']['contact_sub_type'] ) )
			$params['contact_sub_type'] = $field['config']['contact_sub_type'];
		// groups
		if ( isset( $field['config']['civicrm_group'] ) )
			$params['group'] = $field['config']['civicrm_group'];
		// sort name
		if( isset( $search_term ) )
			$params['sort_name'] = $search_term;
		// contact_id
		if ( isset( $contact_id ) ) {
			$params['contact_id'] = $contact_id;
		}

		$result = $this->get_contacts( $params );

		echo json_encode( $result );
		die;
	}

	public function get_contacts( $search, $with_email = false ) {
		
		$params = [
			'sequential' => 1,
			'return' => [ 'sort_name', 'email' ],
			'is_deleted' => 0,
			'is_deceased' => 0,
		];

		$params = array_merge( $params, $search );
		
		$contacts = civicrm_api3( 'Contact', 'get', $params );

		foreach ( $contacts['values'] as $key => $contact ) {
			$sort_name = $with_email ? $contact['sort_name'] . ' :: ' . $contact['email'] : $contact['sort_name'];
			$result[] = [ 'id' => $contact['id'], 'sort_name' => $sort_name ];
		}

		return $result;
	}

	public function civicrm_get_groups() {
		if ( isset( $_POST['search'] ) ) $search_term = $_POST['search'];
		if ( isset( $_POST['group_id'] ) ) $group_id = $_POST['group_id'];

		if ( ! wp_verify_nonce( $_POST['nonce'], 'admin_get_groups' ) ) return;

		$params = [
			'sequential' => 1,
			'return' => [ 'id', 'title' ],
			'is_active' => 1,
		];

		if ( isset( $group_id ) ) $params['id'] = $group_id;
		if ( isset( $search_term ) && ! empty( $search_term ) ) $params['title'] = [ 'LIKE' => '%' . $search_term . '%' ];
		
		$groups = civicrm_api3( 'Group', 'get', $params );
		
		echo json_encode( $groups['values'] );
		die;
	}

	public function flush_price_set_cache() {
		delete_transient( 'cfc_civicrm_price_sets' );
		if ( $this->plugin->helper->cached_price_sets() ) {
			ob_start();
			?>
				<div style="margin-top: 20px;">
					<?php _e( 'Price Sets have been rebuilt, please refresh the page to see the changes.', 'caldera-forms-civicrm' ); ?>
				</div>
			<?php
			$result = ob_get_contents();
			ob_end_clean();
			echo $result;
		}
		die;
	}

	public function do_code_cividiscount() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'civicrm_cividiscount_code' ) ) return;
		if ( isset( $_POST['cividiscount_code'] ) ) $code = $_POST['cividiscount_code'];
		if ( isset( $_POST['form_id'] ) ) $form_id = $_POST['form_id'];
		if ( isset( $_POST['form_id_attr'] ) ) $form_id_attr = $_POST['form_id_attr'];
		
		$discount = $this->plugin->cividiscount->get_by_code( $code );

		if ( $discount ) {
			// form config
			$form = Caldera_Forms::get_form( $form_id );
			// add count
			$form['form_count'] = str_replace( $form['ID'].'_', '', $form_id_attr );

			$discounted_options = $this->plugin->cividiscount->do_code_discount( $discount, $form );

  		}

		echo json_encode( $discounted_options );
		die;
	}

	public function civicrm_get_premiums() {
		if ( isset( $_POST['search'] ) ) $search_term = $_POST['search'];
		if ( isset( $_POST['premium_id'] ) ) $premium_id = $_POST['premium_id'];

		if ( ! wp_verify_nonce( $_POST['nonce'], 'admin_get_premiums' ) ) return;

		$params = [
			'sequential' => 1,
			'is_active' => 1,
		];

		if ( isset( $premium_id ) ) $params['id'] = $premium_id;
		if ( isset( $search_term ) && ! empty( $search_term ) ) $params['name'] = [ 'LIKE' => '%' . $search_term . '%' ];
		
		$premiums = civicrm_api3( 'Product', 'get', $params );
		
		echo json_encode( $premiums['values'] );
		die;
	}
}
