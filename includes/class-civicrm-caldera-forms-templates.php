<?php

/**
 * CiviCRM Caldera Forms Templates Class.
 *
 * @since 0.3
 */
class CiviCRM_Caldera_Forms_Templates {

	/**
	 * Initialises this object.
	 *
	 * @since 0.3
	 */
	public function __construct() {

		// initialise this object
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3
	 */
	public function register_hooks() {

		// adds custom form templates
		add_filter( 'caldera_forms_get_form_templates', [ $this, 'register_templates' ] );

	}

	/**
	 * Register templates.
	 *
	 * @since 0.3
	 *
	 * @param array $templates The existing templates
	 * @return array $templates The modified templates
	 */
	public function register_templates( $templates ) {

		// add basic contact template
		$templates['civicrm_contact_basic'] = [
			'name' => esc_html__( 'CiviCRM Contact Form', 'caldera-forms-civicrm' ),
			'template' => include CF_CIVICRM_INTEGRATION_PATH . 'form-templates/civicrm-contact-basic.php'
		];

		// add contact with address template
		$templates['civicrm_contact_address'] = [
			'name' => esc_html__( 'CiviCRM Contact and Address', 'caldera-forms-civicrm' ),
			'template' => include CF_CIVICRM_INTEGRATION_PATH . 'form-templates/civicrm-contact-address.php'
		];

		// add organisation template
		$templates['civicrm_org_basic'] = [
			'name' => esc_html__( 'CiviCRM Organisation', 'caldera-forms-civicrm' ),
			'template' => include CF_CIVICRM_INTEGRATION_PATH . 'form-templates/civicrm-organisation-basic.php'
		];

		// add organisation with primary contact template
		$templates['civicrm_org_contact'] = [
			'name' => esc_html__( 'CiviCRM Organisation and Contact', 'caldera-forms-civicrm' ),
			'template' => include CF_CIVICRM_INTEGRATION_PATH . 'form-templates/civicrm-organisation-contact.php'
		];

		return $templates;

	}

}
