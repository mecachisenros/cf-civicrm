<?php

/**
 * CiviCRM Caldera Forms Fields Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Fields {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	 * The custom field objects reference array.
	 *
	 * @since 0.2
	 * @access public
	 * @var array $field_objects The custom field objects reference array
	 */
	public $field_objects = [];

	/**
	 * Autopopulate and Bulk insert/Presets field objects array.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $presets_objects Autopopulate and Bulk insert/Presets objects
	 */
	public $presets_objects = [];

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// initialise this object
		$this->include_files();
		$this->setup_objects();

	}

	/**
	 * Include field class files.
	 *
	 * @since 0.2
	 */
	private function include_files() {

		// include field class files
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_country/class-civicrm-country.php';
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_state/class-civicrm-state.php';
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_file/class-civicrm-file.php';
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_contact_reference/class-civicrm-contact-reference.php';
		// include civicrm field presets
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/presets/class-civicrm-core-fields-presets.php';
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/presets/class-civicrm-custom-fields-presets.php';
		if ( in_array( 'CiviContribute', $this->plugin->processors->enabled_components ) )
			include CF_CIVICRM_INTEGRATION_PATH . 'fields/presets/class-civicrm-price-sets-presets.php';
		include CF_CIVICRM_INTEGRATION_PATH . 'fields/discount/class-civicrm-discount.php';
	}

	/**
	 * Initialise field objects.
	 *
	 * @since 0.2
	 */
	private function setup_objects() {

		// add to custom fields array
		$this->field_objects['civicrm_country'] = new CiviCRM_Caldera_Forms_Field_Country( $this->plugin );
		$this->field_objects['civicrm_state'] = new CiviCRM_Caldera_Forms_Field_State( $this->plugin );
		$this->field_objects['civicrm_file'] = new CiviCRM_Caldera_Forms_Field_File( $this->plugin );
		$this->field_objects['civicrm_contact_reference'] = new CiviCRM_Caldera_Forms_Contact_Reference( $this->plugin );
		
		// autopopulate and bulk insert/presets
		$this->presets_objects['civicrm_core_fields'] = new CiviCRM_Caldera_Forms_Core_Fields_Presets( $this->plugin );
		$this->presets_objects['civicrm_custom_fields'] = new CiviCRM_Caldera_Forms_Custom_Fields_Presets( $this->plugin );
		if ( in_array( 'CiviContribute', $this->plugin->processors->enabled_components ) )
			$this->presets_objects['civicrm_price_sets'] = new CiviCRM_Caldera_Forms_Price_Sets_Presets( $this->plugin );

		// discount field for cividiscount integration
		if ( $this->plugin->processors->enabled_extensions && in_array( 'org.civicrm.module.cividiscount', $this->plugin->processors->enabled_extensions ) )
			$this->field_objects['civicrm_discount'] = new CiviCRM_Caldera_Forms_Field_Discount( $this->plugin );

	}
}
