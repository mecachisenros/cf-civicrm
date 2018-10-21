<?php

/**
 * CiviCRM Caldera Forms Note Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Note_Processor {

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
	public $key_name = 'civicrm_note';

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
			'name' => __( 'CiviCRM Note', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM note to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/note/note_config.php',
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
	public function pre_processor( $config, $form, $proccesid ) {

		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		if ( ! empty( $form_values ) ) {
			$form_values['entity_id'] = $transient->contacts->{$this->contact_link}; // Contact ID set in Contact Processor
			$form_values['modified_date'] = date( 'YmdHis', strtotime( 'now' ) );
			// Add Note to contact
			try {
				$note = civicrm_api3( 'Note', 'create', $form_values );
			} catch ( CiviCRM_API3_Exception $e ) {
				$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
				return [ 'note' => $error, 'type' => 'error' ];
			}

			/**
			 * Handle File/Advanced File fields for attachments.
			 * @since 0.4.2
			 */
			if ( ! empty( $config['note_attachment'] ) )
				$this->plugin->helper->handle_file_attachments_core( 
					'civicrm_note',
					$note['id'],
					$config['note_attachment'],
					$form
				);

		}
	}
}
