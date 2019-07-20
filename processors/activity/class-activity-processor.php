<?php

/**
 * CiviCRM Caldera Forms Activity Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Activity_Processor {

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
	public $key_name = 'civicrm_activity';

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
			'name' => __( 'CiviCRM Activity', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM activity to contact', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/activity/activity_config.php',
			'processor' =>  [ $this, 'processor' ],
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
	 * @param string $processid The process id
	 */
	public function processor( $config, $form, $proccesid ) {

		global $transdata;

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		if( ! empty( $form_values ) ) {
			$form_values['activity_type_id'] = $config['activity_type_id']; // Activity Type ID
			$form_values['status_id'] = $config['status_id']; // Activity Status ID
			$form_values['campaign_id'] = $config['campaign_id']; // Campaign ID
			$form_values['source_contact_id'] = $transient->contacts->{$this->contact_link}; // Default to Contact link

			foreach ( $config as $name => $value ) {
				if ( in_array( $name, [ 'target_contact_id', 'source_contact_id', 'assignee_contact_id' ] ) && ! empty( $value ) )
					$form_values[$name] = strpos( $value, 'contact_' ) !== false ? $transient->contacts->{'cid_' . str_replace( 'contact_', '', $value )} : $value;
			}

			// FIXME
			// Concatenate DATE + TIME
			// $form_values['activity_date_time'] = $form_values['activity_date_time'];

			// error message when case_id magic tag hasn't been evaluated
			if ( isset( $form_values['case_id'] ) && ! is_numeric( $form_values['case_id'] ) ) {
				$notice = __( 'Activity not created due to invalid case_id.', 'cf-civicrm' );
				$transdata['error'] = TRUE;
				$transdata['note'] = $notice;
				return;
			}

			try {
				$activity = civicrm_api3( 'Activity', 'create', $form_values );
			} catch ( CiviCRM_API3_Exception $e ) {
				$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
				$transdata['error'] = TRUE;
				$transdata['note'] = $error;
				return;
			}

			/**
			 * Handle File/Advanced File fields for attachments.
			 * @since 0.4.2
			 */
			if ( ! empty( $config['file_id'] ) )
				$this->plugin->helper->handle_file_attachments_core(
					'civicrm_activity',
					$activity['id'],
					$config['file_id'],
					$form
				);

		}
	}
}
