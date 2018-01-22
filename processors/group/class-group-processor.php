<?php

/**
 * CiviCRM Caldera Forms Group Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Group_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_group';

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

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
			'name' => __( 'CiviCRM Group', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM contact to group', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/group/group_config.php',
			'pre_processor' => array( $this, 'pre_processor' ),
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
	public function pre_processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		// Add Contact to group
		try {
			$result = civicrm_api3( 'GroupContact', 'create', array(
				'sequential' => 1,
				'group_id' => $config['contact_group'], // Group ID from processor config
				'contact_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']], // Contact ID set in Contact Processor
			));
		} catch ( CiviCRM_API3_Exception $e ) {
			$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
			return array( 'note' => $error, 'type' => 'error' );
		}

	}

	/**
	 * CiviCRM Group fields callback function.
	 *
	 * This is used by 'Caldera_Forms_Processor_UI' class to build processor fields.
	 *
	 * Unused at present.
	 *
	 * @see https://gist.github.com/Shelob9/ee2210ad15f66aee40acdc8fd23f3348
	 *
	 * @since 0.2
	 *
	 * @return array $groups Fields configuration
	 */
	public function group_fields() {

		$groupsResult = civicrm_api3( 'Group', 'get', array(
			'sequential' => 1,
			'cache_date' => null,
			'is_active' => 1,
			'options' => array( 'limit' => 0 ),
		));

		$groups = array();
		foreach ( $groupsResult['values'] as $key => $value ) {
			$group['id'] = $value['name'];
			$group['label'] = $value['title'];
			$groups[] = $group;
		}
		return $groups;

	}

}
