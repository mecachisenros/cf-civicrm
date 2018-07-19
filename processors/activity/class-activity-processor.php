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
			'name' => __( 'CiviCRM Activity', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM activity to contact', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/activity/activity_config.php',
			'pre_processor' =>  [ $this, 'pre_processor' ],
		];

		return $processors;

	}

	public static $transient_id;

	/**
	 * Form processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function pre_processor( $config, $form, $proccesid ) {
		global $transdata;
		// cfc transient object
		// $transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		$transient = self::$transient_id ? $this->plugin->transient->get( self::$transient_id ) : $this->plugin->transient->get();
		
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

		 	$create_activity = $this->plugin->api->wrapper( 'Activity', 'create', $form_values );

			if ( ! empty( $config['file_id'] ) ) {

				$transdata['civicrm']['civicrm_files'] = $this->plugin->helper->get_file_entity_ids();

				if ( is_array( $transdata['data'][$config['file_id']] ) ) {

					// handle multiple upload file 'advanced_file', limit to 3 files
					$file_ids = $transdata['data'][$config['file_id']];
					for ( $x = 0; $x < $this->plugin->helper->get_civicrm_settings( 'max_attachments' ); $x++ ) {
  						$this->plugin->helper->create_civicrm_entity_file( 'civicrm_activity', $create_activity['id'], $file_ids[$x] );
					}

				} else {
					// single file
					foreach ( $transdata['civicrm']['civicrm_files'] as $field_number => $file ) {
						if ( $config['file_id'] == $file['field_id'] && ! empty( $file['file_id'] ) ) {

							$this->plugin->helper->create_civicrm_entity_file( 'civicrm_activity', $create_activity['id'], $file['file_id'] );

						}
					}
				}
			}
		}
	}
}
