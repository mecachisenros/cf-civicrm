<?php

/**
 * CiviCRM Caldera Forms Group Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Group_Processor {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $plugin The plugin instance
	 */
	public $plugin;

	/**
	 * Contact link.
	 *
	 * @since 0.4.4
	 * @access protected
	 * @var string $contact_link The contact link
	 */
	protected $contact_link;

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );

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

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Group', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM contact to group', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/group/group_config.php',
			'processor' => [ $this, 'processor' ],
		];

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

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Add Contact to group
		try {
			if ( empty($config['double_optin']) ) {
				$result = civicrm_api3( 'GroupContact', 'create', [
					'sequential' => 1,
					'group_id' => $config['contact_group'], // Group ID from processor config
					'contact_id' => $transient->contacts->{$this->contact_link}, // Contact ID set in Contact Processor
				] );
			} else {
				$contact_id = $transient->contacts->{$this->contact_link};
				$email_result = civicrm_api3( 'Email', 'get', [
					'sequential' => 1,
					'return' => ['email'],
					'contact_id' => $contact_id,
					'is_primary' => 1,
				] );

				$result = civicrm_api3( 'MailingEventSubscribe', 'create', [
					'sequential' => 1,
					'group_id' => $config['contact_group'], // Group ID from processor config
					'contact_id' => $contact_id, // Contact ID set in Contact Processor
					'email' => $email_result['values'][0]['email']
				] );
			}
		} catch ( CiviCRM_API3_Exception $e ) {
			global $transdata;
			$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
			$transdata['error'] = true;
			$transdata['note'] = $error;
			return;
		}

	}
}
