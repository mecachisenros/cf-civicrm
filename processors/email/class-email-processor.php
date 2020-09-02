<?php

/**
 * CiviCRM Caldera Forms Email Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Email_Processor {

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
	public $key_name = 'civicrm_email';

	/**
	 * Fields to ignore while prepopulating
	 *
	 * @since 0.4
	 * @access public
	 * @var array $fields_to_ignore Fields to ignore
	 */
	public $fields_to_ignore = [ 'contact_link', 'location_type_id' ];

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// filter form before rendering
		add_filter( 'caldera_forms_render_get_form', [ $this, 'pre_render' ] );

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
			'name' => __( 'CiviCRM Email', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM email to contacts', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/email/email_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
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
	public function pre_processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		if ( ! empty( $transient->contacts->{$this->contact_link} ) ) {

			try {

				$email = civicrm_api3( 'Email', 'getsingle', [
					'sequential' => 1,
					'contact_id' => $transient->contacts->{$this->contact_link},
					'location_type_id' => $config['location_type_id'],
				] );

			} catch ( CiviCRM_API3_Exception $e ) {
				// Ignore if none found
			}

			// Get form values
			$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

			if( ! empty( $form_values ) ) {
				$form_values['contact_id'] = $transient->contacts->{$this->contact_link}; // Contact ID set in Contact Processor

				// Pass Email ID if we got one
				if ( isset( $email ) && is_array( $email ) ) {
					$form_values['id'] = $email['id']; // Email ID
				} else {
					$form_values['location_type_id'] = $config['location_type_id']; // Email Location Type
				}

				try {
					$create_email = civicrm_api3( 'Email', 'create', $form_values );
				} catch ( CiviCRM_API3_Exception $e) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		}
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

		// continue as normal if form has no processors
		if( empty( $form['processors'] ) ) return $form;

		// cfc transient object
		$transient = $this->plugin->transient->get();

		foreach ( $form['processors'] as $processor => $pr_id ) {
			if( $pr_id['type'] == $this->key_name ){

				$contact_link = $pr_id['contact_link'] = 'cid_'.$pr_id['config']['contact_link'];

				if ( isset( $transient->contacts->{$contact_link} ) ) {
					try {

						$contact_email = civicrm_api3( 'Email', 'getsingle', [
							'sequential' => 1,
							'contact_id' => $transient->contacts->{$contact_link},
							'location_type_id' => $pr_id['config']['location_type_id'],
						] );

					} catch ( CiviCRM_API3_Exception $e ) {
						// Ignore if we have more than one email with same location type or none
					}
				}

				if ( isset( $contact_email ) && ! isset( $contact_email['count'] ) ) {
					$form = $this->plugin->helper->map_fields_to_prerender(
						$pr_id['config'],
						$form,
						$this->fields_to_ignore,
						$contact_email
					);
				}

				// Clear Email data
				unset( $contact_email );
			}
		}

		return $form;
	}
}
