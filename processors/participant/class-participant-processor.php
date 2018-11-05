<?php

/**
 * CiviCRM Caldera Forms Participant Processor Class.
 * @since 1.0
 */
class CiviCRM_Caldera_Forms_Participant_Processor {

	/**
	 * Plugin reference.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $plugin The plugin instance
	 */
	public $plugin;

	/**
	 * Contact link.
	 * 
	 * @since 1.0
	 * @access protected
	 * @var string $contact_link The contact link
	 */
	protected $contact_link;

	/**
	 * Event Ids, array holding event ids indexed by procesor id.
	 *
	 * @access public
	 * @since 1.0
	 * @var array $event_ids
	 */
	public $event_ids;

	/**
	 * Events data, array holding event settings indexed by processor id.
	 *
	 * @access public
	 * @since 1.0
	 * @var array $events
	 */
	public $events;

	/**
	 * Current registration for a contact (participant data).
	 *
	 * @access public
	 * @since 1.0
	 * @var array $registrations
	 */
	public $registrations;

	/**
	 * Reference to the form fields set as price field options, indexed by processor id.
	 *
	 * @access public
	 * @since 1.0
	 * @var array $price_field_refs
	 */
	public $price_field_refs = [];

	/**
	 * The processor key.
	 *
	 * @since 1.0
	 * @access public
	 * @var string $key_name The processor key
	 */
	public $key_name = 'civicrm_participant';

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

		// build price field references, at both render and submission start
		add_filter( 'caldera_forms_render_get_form', [ $this, 'get_set_necessary_data' ] );
		add_filter( 'caldera_forms_submit_get_form', [ $this, 'get_set_necessary_data' ], 20 );

		// filter fields for notices and discount price fields
		add_filter( 'caldera_forms_render_get_field', [ $this, 'filter_fields_config' ], 10, 2 );
		add_filter( 'caldera_forms_render_setup_field', [ $this, 'filter_fields_config' ], 10, 2 );
		add_filter( 'caldera_forms_render_field_structure', [ $this, 'filter_fields_config' ], 10, 2 );

		// filter form when it renders
		add_filter( 'caldera_forms_render_get_form', [ $this, 'pre_render' ] );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 1.0
	 * @uses 'caldera_forms_get_form_processors' filter
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Participant', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM Participant to event (for Event registration).', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/participant/config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'magic_tags' => [ 'processor_id' ]
		];

		return $processors;

	}

	/**
	 * Form pre processor callback.
	 *
	 * @since 1.0
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );


		if ( ! empty( $transient->contacts->{$this->contact_link} ) ) {
			// event
			$event = $this->events[$config['id']];
			
			$form_values['contact_id'] = $transient->contacts->{$this->contact_link};
			$form_values['event_id'] = $config['id'];
			$form_values['role_id'] = ( $config['role_id'] == 'default_role_id' ) ? $event['default_role_id'] : $config['role_id'];
			$form_values['status_id'] = ( $config['status_id'] == 'default_status_id' ) ? 'Registered' : $config['status_id']; // default is registered

			// if multiple participant processors, we need to update $this->registrations
			$this->is_registered_for( $form );

			// check if should register participant
			$notice = $this->get_notice( $config['processor_id'], $form );
			if ( $notice ) {
				$notice['type'] = 'error';
				return $notice;
			}
			
			// store data in transient
			$transient->participants->{$config['processor_id']}->params = $form_values;
			$this->plugin->transient->save( $transient->ID, $transient );

			if ( ! $config['is_monetary'] ) {
				try {
					$create_participant = civicrm_api3( 'Participant', 'create', $form_values );
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		}

	}

	/**
	 * Form processor callback.
	 *
	 * @since 1.0
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function processor( $config, $form, $porcessid ) {
		return [ 'processor_id' => $config['processor_id'] ];
	}

	/**
	 * Autopopulates Form with Civi data.
	 *
	 * @uses 'caldera_forms_render_get_form' filter
	 * @since 1.0
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function pre_render( $form ) {

		// render notices for non paid events
		$this->render_notices_for_non_paid_events( $form );

		return $form;
	}

	public function get_set_necessary_data( $form ) {

		// get events data
		$this->get_set_events( $form );
		// build price field references
		$this->build_price_field_refs( $form );
		// get event registrations
		$this->is_registered_for( $form );

		return $form;
	}

	/**
	 * Build Price Field fields references from Line Item processors for paid events.
	 *
	 * Stores a referene to the participant processor and the price field
	 * set in it's corresponding line item processor.
	 *
	 * @since 1.0
	 * @param array $form The form config
	 * @return array $form The form config
	 */
	public function build_price_field_refs( $form ) {

		if ( ! empty( $this->price_field_refs ) ) return $this->price_field_refs;

		// participant processors
		$participants = $this->plugin->helper->get_processor_by_type( $this->key_name, $form );

		if ( ! $participants ) return;

		// line item processors
		$line_items = $this->plugin->helper->get_processor_by_type( 'civicrm_line_item', $form );

		if ( ! $line_items ) return;

		$this->price_field_refs = array_reduce( $line_items, function( $refs, $line_item ) use ( $form ) {

			if ( $line_item['config']['entity_table'] == $this->key_name ) {
				// price_field field config
				$price_field_field = Caldera_Forms_Field_Util::get_field_by_slug( str_replace( '%', '', $line_item['config']['price_field_value'] ), $form );
				// participant processor id
				$participant_pid = $this->plugin->helper->get_processor_from_magic( $line_item['config']['entity_params'], $form );
				
				$refs[$participant_pid] = $price_field_field['ID'];

				return $refs;
			}

		}, [] );

	}

	/**
	 * Get and set events.
	 * 
	 * @since 1.0
	 * @param int $id Event id
	 * @return array|boolean $event The event settings, or false
	 */
	public function get_set_events( $form ) {

		if ( ! empty( $this->events ) ) return $this->events;
		// participant processors
		$processors = $this->plugin->helper->get_processor_by_type( $this->key_name, $form );

		if ( ! $processors ) return;

		// event ids set in form's participant processors
		$this->event_ids = array_reduce( $processors, function( $event_ids, $processor ) {
			$event_ids[$processor['ID']] = $processor['config']['id'];
			return $event_ids;
		}, [] );

		// get events
		try {
			$events = civicrm_api3( 'Event', 'get', [
				'sequential' => 1,
				'id' => [ 'IN' => array_values( $this->event_ids ) ]
			] );
		} catch ( CiviCRM_API3_Exception $e ) {

		}

		if ( $events['count'] ) {
			// set events references
			$this->events = array_reduce( $events['values'], function( $events, $event ) {
				$event['participant_count'] = CRM_Event_BAO_Event::getParticipantCount( $event['id'] );
				$events[array_search( $event['id'], $this->event_ids )] = $event;
				return $events;
			}, [] );
		}

	}

	/**
	 * Alter discounted price sets/price fields options and append notices for paid events.
	 *
	 * If it's a paid event, we know what field in the form
	 * is setup as a price set/price field accessing $price_field_refs.
	 *
	 * @uses 'caldera_forms_render_get_field' filter
	 * @since 1.0
	 * @param array $field The field config
	 * @param array $form The form config
	 * @return array $field The field config
	 */
	public function filter_fields_config( $field, $form ) {

		// participant processors
		$processors = $this->plugin->helper->get_processor_by_type( $this->key_name, $form );

		// continue if no participants are present
		if ( ! $processors ) return $field;
		// field or field_structure
		$field_ID = $field['id'] ? $field['id'] : $field['ID'];
		// only if the current field is mapped to a price_set/price_field
		if ( array_search( $field_ID, $this->price_field_refs ) ) {

			array_map( function( $processor_id, $field_id ) use ( &$field, $form, $processors ) {

				// only paid events will have a price set/price field
				if ( ! $processors[$processor_id]['config']['is_monetary'] ) return;

				// append notice only at render structure stage
				if ( current_filter() == 'caldera_forms_render_field_structure' && $field_id == $field['id'] ) {

					$notice = $this->get_notice( $processor_id, $form );

					if ( ! $notice ) return;
					
					// notice html
					$template_path = CF_CIVICRM_INTEGRATION_PATH . 'templates/notice.php';
					$html = $this->plugin->html->generate( $notice, $template_path );

					if ( $notice['disabled'] ) {
						$field['field_before'] = '<fieldset disabled="true">' . $field['field_before'];
						$field['field_after'] = $field['field_after'] . '</fieldset>';
					}

					$field['label_after'] = $field['label_after'] . $html;

				}

				// check for discounted price field at field setup and config stages
				if ( ( current_filter() == 'caldera_forms_render_get_field' || current_filter() == 'caldera_forms_render_setup_field' ) && $field_id == $field['ID'] ) {

					// filter required config at setup stage as well
					if ( $this->get_notice( $processor_id, $form )['disabled'] ) $field['required'] = 0;

					// get discount entity id
					$discount_entity_id = CRM_Core_BAO_Discount::findSet( $processors[$processor_id]['config']['id'], 'civicrm_event' );

					// alter price field options if we have a discount
					if ( $discount_entity_id ) {
						// get price set id for discounted price set
						$price_set_id = CRM_Core_DAO::getFieldValue( 'CRM_Core_BAO_Discount', $discount_entity_id, 'price_set_id' );
						$price_set = $this->plugin->helper->cached_price_sets()[$price_set_id];
						// there's only one price field per discounted price set
						$price_field = reset( $price_set['price_fields'] );

						// filter field options
						$field['config']['option'] = array_reduce( $price_field['price_field_values'], function( $options, $price_field_value ) use ( $field ) {
							$options[$price_field_value['id']] = [
								'value' => $price_field_value['id'],
								'label' => $price_field_value['label'] . ' - ' . $field['config']['price_field_currency'] . ' ' . $price_field_value['amount'],
								'calc_value' => $price_field_value['amount']
							];
							return $options;
						}, [] );

					}
				}

			}, array_keys( $this->price_field_refs ), $this->price_field_refs );
		}

		return $field;
	}

	/**
	 * Get notice for participant processor.
	 *
	 * @since 1.0
	 * @param string $processor_id The processor id
	 * @param array $form The form config
	 * @param boolean $add_filter Wheather to add 'cfc_notices_to_render' filter
	 * @return array $notice Notice data array
	 */
	public function get_notice( $processor_id, $form, $add_filter = false ) {

		$processor = $form['processors'][$processor_id];
		$event = $this->events[$processor_id];
		$participant = $this->registrations[$processor_id];

		// notices filter
		$filter = 'cfc_notices_to_render';
		// cfc_notices_to_render filter callback
		$callback = function( $notices ) use ( $event, &$notice ) {
			$notices[] = $notice;
			return $notices;
		};

		// is registered
		if ( $participant && $participant['event_id'] == $event['id'] ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( 'Oops. It looks like you are already registered for the event <strong>%1$s</strong>. If you want to change your registration, or you think that this is an error, please contact the site administrator.', 'caldera-forms-civicrm' ), $event['title'] ),
				'disabled' => true
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

		// registration start date
		if ( isset( $event['registration_start_date'] ) && date( 'Y-m-d H:i:s' ) <= $event['registration_start_date'] ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( 'Registration for the event <strong>%s</strong> is not yet opened.', 'caldera-forms-civicrm' ), $event['title'] ),
				'disabled' => true
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

		// registration end date
		if ( isset( $event['registration_end_date'] ) && date( 'Y-m-d H:i:s' ) >= $event['registration_end_date']  ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( 'Registration for the event <strong>%1$s</strong> was closed on %2$s.', 'caldera-forms-civicrm' ), $event['title'], date_format( date_create( $event['registration_end_date'] ), 'F d, Y H:i' ) ),
				'disabled' => true
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

		// is participant approval
		if ( $event['requires_approval'] ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( '%s', 'caldera-forms-civicrm' ), $event['approval_req_text'] ),
				'disabled' => false
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

		// has waitlist and is full
		if ( $this->is_full( $event ) && $event['has_waitlist'] ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( '%s', 'caldera-forms-civicrm' ), $event['waitlist_text'] ),
				'disabled' => false
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

		// event full
		if ( $this->is_full( $event ) && ! $event['has_waitlist'] ) {
			$notice = [
				'type' => 'warning',
				'note' => sprintf( __( '%s', 'caldera-forms-civicrm' ), $event['event_full_text'] ),
				'disabled' => true
			];

			if ( ! $add_filter ) return $notice;
			// render notices
			add_filter( $filter, $callback );
			return;
		}

	}

	public function render_notices_for_non_paid_events( $form ) {

		// participant processors
		$processors = $this->plugin->helper->get_processor_by_type( $this->key_name, $form );

		if ( ! $processors ) return;

		array_map( function( $processor_id, $processor ) use( $form ) {

			// only for non paid events
			if ( $processor['config']['is_monetary'] ) return;

			$this->get_notice( $processor_id, $form, $add_filter = true );

		}, array_keys( $processors ), $processors );

	}

	/**
	 * Get event.
	 *
	 * @since 1.0
	 * @param int $id Event id
	 * @return array|boolean $event The event settings, or false
	 */
	public function get_event( $id ) {

		try {
			$event = civicrm_api3( 'Event', 'getsingle', [ 'id' => $id ] );
		} catch( CiviCRM_API3_Exception $e ) {
			$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
			return [ 'note' => $error, 'type' => 'error' ];
		}

		if ( is_array( $event ) && ! $event['is_error'] ) {
			// get count
			$event['participant_count'] = CRM_Event_BAO_Event::getParticipantCount( $id );
			return $event;
		}

		return false;
	}

	/**
	 * Event is full.
	 *
	 * @since 1.0
	 * @param array $event The event config
	 * @return boolean True if full, false otherwise
	 */
	public function is_full( $event ) {
		if ( isset( $event['participant_count'] ) && isset( $event['max_participants'] ) )
			return $event['participant_count'] >= $event['max_participants'];
	}

	/**
	 * Get default status.
	 *
	 * @since 1.0
	 * @param array $event The event config
	 * @param array $config Processor config
	 * @return string $status The participant status
	 */
	public function default_status( $event, $config ) {

		if ( $config['status_id'] != 'default_status_id' ) return $config['status_id'];

		if ( $event['requires_approval'] ) return 'Awaiting approval';

		if ( $event['has_waitlist'] && $this->is_full( $event ) ) return 'On waitlist';

		return 'Registered';
	}

	/**
	 * Get contact event registrations.
	 *
	 * @since 1.0
	 * @param array $form The form config
	 */
	public function is_registered_for( $form ) {

		// participant processors
		$processors = $this->plugin->helper->get_processor_by_type( $this->key_name, $form );

		if ( ! $processors ) return;

		// contact processors
		$contacts = $this->plugin->helper->get_processor_by_type( 'civicrm_contact', $form );
		// return registrations if is set and only one contact, otherwise recalculate
		if ( count( $contacts ) == 1 && ! empty( $this->registrations ) ) return $this->registrations;

		// cfc transient
		$transient = $this->plugin->transient->get();

		array_map( function( $processor ) use ( $transient ) {

			if ( ! isset( $processor['runtimes'] ) ) return;

			$contact_link = 'cid_' . $processor['config']['contact_link'];

			if ( ! isset( $transient->contacts->{$contact_link} ) || empty( $transient->contacts->{$contact_link} ) ) return;

			$params = [
				'contact_id' => $transient->contacts->{$contact_link},
				'event_id' => [ 'IN' => array_values( $this->event_ids ) ]
			];

			try {
				$participant = civicrm_api3( 'Participant', 'get', $params );
			} catch ( CiviCRM_API3_Exception $e ) {

			}

			if ( $participant['count'] ) {
				array_map( function( $participant ) use ( $processor ) {

					$event_id = $this->event_ids[$processor['ID']];

					if ( $participant['event_id'] == $event_id )
						$this->registrations[$processor['ID']] = $participant;

				}, $participant['values'] );
			}

		}, $processors );
	}

}
