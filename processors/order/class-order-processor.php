<?php

/**
 * CiviCRM Caldera Forms Order Processor Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Order_Processor {

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
	 * Payment processor fee.
	 *
	 * @since 0.4.4
	 * @access protected
	 * @var string $fee The fee
	 */
	protected $charge_metadata;

	/**
	 * Is pay later.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var boolean $is_pay_later
	 */
	public $is_pay_later;

	/**
	 * Total tax amount.
	 *
	 * @since 1.0.1
	 * @access public
	 * @var float $total_tax_amount
	 */
	public $total_tax_amount = 0;

	/**
	 * The order result.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $order
	 */
	public $order;

	/**
	 * The processor key.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_order';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// add payment processor hooks
		add_action( 'caldera_forms_submit_pre_process_start', [ $this, 'add_payment_processor_hooks' ], 10, 3 );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.4
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Order', 'cf-civicrm' ),
			'description' => __( 'Add CiviCRM Order (Contribution with multiple Line Items, ie Events registrations, Donations, Memberships, etc.)', 'cf-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/order/order_config.php',
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'post_processor' => [ $this, 'post_processor'],
			'magic_tags' => [ 'order_id', 'processor_id' ]
		];

		return $processors;

	}

	/**
	 * Form pre processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function processor( $config, $form, $processid ) {

		global $transdata;

		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		$form_values['financial_type_id'] = $config['financial_type_id'];
		$form_values['contribution_status_id'] = $config['contribution_status_id'];
		$form_values['payment_instrument_id'] = ! isset( $config['is_mapped_field'] ) ?
			$config['payment_instrument_id'] :
			$form_values['mapped_payment_instrument_id'];

		$form_values['currency'] = $config['currency'];

		if ( ! empty( $config['campaign_id'] ) ) $form_values['campaign_id'] = $config['campaign_id'];

		// contribution page for reciepts
		if ( isset( $config['contribution_page_id'] ) )
			$form_values['contribution_page_id'] = $config['contribution_page_id'];

		// is pay later
		if ( isset( $config['is_pay_later'] ) && in_array( $form_values['payment_instrument_id'], [$config['is_pay_later']] ) ) {
			$this->is_pay_later = true;
			$form_values['contribution_status_id'] = 'Pending';
			$form_values['is_pay_later'] = 1; // has to be set, if not we get a (Incomplete transaction)
			unset( $form_values['trxn_id'] );
		}

		// source
		if( ! isset( $form_values['source'] ) )
			$form_values['source'] = $form['name'];

		$form_values['contact_id'] = $transient->contacts->{$this->contact_link};

		// line items
		$line_items = $this->build_line_items_params( $transient, $config, $form );

		if ( $this->has_participant_item( $line_items ) )
			$line_items = $this->maybe_format_line_items_to_entity( $line_items, $config, $form );

		// add tax amount
		if ( $this->total_tax_amount )
			$form_values['tax_amount'] = $this->total_tax_amount;

		// stripe metadata
		if ( $this->charge_metadata ) $form_values = array_merge( $form_values, $this->charge_metadata );

		// FIXME
		// move this into its own finction
		//
		// authorize metadata
		if( isset( $transdata[$transdata['transient']]['transaction_data']->transaction_id ) ) {
			$metadata = [
				'trxn_id' => $transdata[$transdata['transient']]['transaction_data']->transaction_id,
				'card_type_id' => $this->get_option_by_label( $transdata[$transdata['transient']]['transaction_data']->card_type ),
				'credit_card_type' => $transdata[$transdata['transient']]['transaction_data']->card_type,
				'pan_truncation' => str_replace( 'X', '', $transdata[$transdata['transient']]['transaction_data']->account_number ),
			];
			$form_values = array_merge( $form_values, $metadata );
		}

		$form_values['line_items'] = $line_items;

		try {
			$create_order = civicrm_api3( 'Order', 'create', $form_values );

			$this->order = ( $create_order['count'] && ! $create_order['is_error'] ) ? $create_order['values'][$create_order['id']] : false;

			// create product
			if ( $this->order ) {
				$this->create_premium( $this->order, $form_values, $config );

				// save orde data in transient
				$transient->orders->{$config['processor_id']}->params = $this->order;
				$this->plugin->transient->save( $transient->ID, $transient );
			}

		} catch ( CiviCRM_API3_Exception $e ) {
			$transdata['error'] = true;
			$transdata['note'] = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
		}

		// return order_id magic tag
		if ( is_array( $create_order ) && ! $create_order['is_error'] ){
			return [
				'order_id' => $create_order['id'],
				'processor_id' => $config['processor_id']
			];
		}

	}

	/**
	 * Form post processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function post_processor( $config, $form, $processid ) {

		global $transdata;
		$transient = $this->plugin->transient->get();

		// preserve join dates
		$this->preserve_membership_join_date( $form );

		$line_items = civicrm_api3( 'LineItem', 'get', [
			'contribution_id' => $this->order['id']
		] );

		$this->order = array_merge( $this->order, [ 'line_items' => $line_items['values'] ] );

		if ( true ) { //$config['is_thank_you'] ) {
			add_filter( 'caldera_forms_ajax_return', function( $out, $_form ) use ( $transdata, $transient ){

				/**
				 * Filter thank you template path.
				 *
				 * @since 0.4.4
				 *
				 * @param string $template_path The template path
				 * @param array $form Form config
				 */
				$template_path = apply_filters( 'cfc_order_thank_you_template_path', CF_CIVICRM_INTEGRATION_PATH . 'templates/thank-you.php', $_form );

				$form_values = Caldera_Forms::get_submission_data( $_form );

				$data = [
					'values' => $form_values,
					'order' => $this->order,
					'form' => $_form,
					'transdata' => $transdata,
					'transient' => $transient
				];

				$html = $this->plugin->html->generate( $data, $template_path, $this->plugin );

				$out['html'] = $out['html'] . $html;

				return $out;

			}, 10, 2 );
		}

		/**
		 * Runs when Order processor is post_processed if an order has been created.
		 *
		 * @since 1.0
		 * @param array $order The created order result
		 * @param array $config The processor config
		 * @param array $form The form config
		 * @param string $processid The process id
		 */
		do_action( 'cfc_order_post_processor', $this->order, $config, $form, $processid );

		// send confirmation/receipt
		$this->maybe_send_confirmation( $this->order, $config );

	}

	/**
	 * Builds line items parameters array formatted for Order.create.
	 *
	 * @since 1.0.1
	 * @param object $transient The Caldera Forms CiviCRM transient object
	 * @param array $config The processor config
	 * @param array $form The form config
	 * @return array $line_items The formatted line items array
	 */
	public function build_line_items_params( $transient, $config, $form ) {

		if ( empty( $config['line_items'] ) ) return [];

		return array_reduce( $config['line_items'], function( $line_items, $item_processor_tag ) use ( $transient, $form ) {

			if ( empty( $item_processor_tag ) ) return $line_items;

			$item_processor_id = Caldera_Forms::do_magic_tags( $item_processor_tag );

			if ( strpos( $item_processor_id, 'civicrm_line_item' ) || empty( ( array ) $transient->line_items->$item_processor_id ) ) return $line_items;

			$line_item = $transient->line_items->$item_processor_id->params;

			if ( isset( $line_item['line_item'][0]['tax_amount'] ) && $this->plugin->helper->get_tax_invoicing() )
				$this->total_tax_amount += $line_item['line_item'][0]['tax_amount'];

			// set membership as pending
			if ( isset( $line_item['params']['membership_type_id'] ) && $this->is_pay_later ) {
				if ( ! $line_item['params']['id'] ) {
					$line_item['params']['status_id'] = 'Pending';
				} else {
					$line_item['params']['num_terms'] = 0;
				}
				$line_item['params']['is_pay_later'] = 1;
				$line_item['params']['skipStatusCal'] = 1;
			}

			// set participant as pending
			if ( isset( $line_item['params']['event_id'] ) && $this->is_pay_later )
				$line_item['params']['status_id'] = 'Pending from pay later';

			// less line item total errors removing entities that are not being processed
			if ( isset( $line_item['processor_entity'] ) && ! empty( $line_item['processor_entity'] ) ) {
				$processor_entity = Caldera_Forms::do_magic_tags( $line_item['processor_entity'] );
				if ( strpos( $processor_entity, 'civicrm' ) ) unset( $line_item['processor_entity'] );
			}

			$line_item['processor_id'] = $item_processor_id;

			if ( isset( $line_item['line_item'] ) )
				$line_items[] = $line_item;

			return $line_items;

		}, [] );

	}

	/**
	 * Reformats the line items to correctly add otpions like donations
	 * assigned to the right enity, participant in this case.
	 *
	 * @since 1.0.1
	 * @param array $line_items The formated line items
	 * @param array $config The processor config
	 * @param array $form The form config
	 * @return array $line_items The reformatted line items
	 */
	public function maybe_format_line_items_to_entity( $line_items, $config, $form ) {

		$participant_processors = $this->plugin->helper->get_processor_by_type( 'civicrm_participant', $form );
		$membership_pprocessors = $this->plugin->helper->get_processor_by_type( 'civicrm_membership', $form );
		$processors = [];

		$transient = $this->plugin->transient->get();

		if ( is_array( $participant_processors ) )
			 $processors = array_merge( $processors, $participant_processors );

		if ( is_array( $membership_pprocessors ) )
			 $processors = array_merge( $processors, $membership_pprocessors );

		if ( empty( $processors ) ) return $line_items;

		$formatted_items = [];

		array_map( function( $item ) use ( &$formatted_items, $processors, $transient ) {

			if ( ! isset( $item['processor_entity'] ) || empty( $item['processor_entity'] ) ) {

				$formatted_items[$item['processor_id']] = $item;

			} else {

				$item['line_item'] = array_map( function( $line ) use ( $item, $processors, $transient ) {
					// only override entity_table for participants being processed
					if ( ! empty( ( array ) $transient->participants->{$item['processor_entity']} ) )
						$line['entity_table'] = $processors[$item['processor_entity']]['type'];

					return $line;

				}, $item['line_item'] );

				if ( isset( $formatted_items[$item['processor_entity']]['line_item'] ) ) {

					$formatted_items[$item['processor_entity']]['line_item'] = array_reduce( $item['line_item'], function( $lines, $line ) {

						$price_field_values_ids = array_column( $lines, 'price_field_value_id' );

						// there cannot be duplicated price field options for the same item
						if ( ! in_array( $line['price_field_value_id'], $price_field_values_ids ) )
							$lines[] = $line;

						return $lines;

					}, $formatted_items[$item['processor_entity']]['line_item'] );

				} else {

					$formatted_items[$item['processor_entity']]['line_item'] = $item['line_item'];

				}

				if ( ! isset( $formatted_items[$item['processor_entity']]['params'] ) && isset( $item['params'] ) )
					$formatted_items[$item['processor_entity']]['params'] = $item['params'];

				// recalculate all item selections for this line_item and update fee amount
				if ( isset( $formatted_items[$item['processor_entity']]['params'] ) ) {

					$fees = array_column( $formatted_items[$item['processor_entity']]['line_item'], 'line_total' );

					$taxes = array_column( $formatted_items[$item['processor_entity']]['line_item'], 'tax_amount' );

					$formatted_items[$item['processor_entity']]['params']['fee_amount'] = ! empty( $taxes )
						? array_sum( array_merge( $fees, $taxes ) )
						: array_sum( $fees );

				}

			}

		}, $line_items );

		return $formatted_items;

	}

	/**
	 * Preserve join date for current membership being processed.
	 *
	 * Background, implemented for new memberships considered as renewals to keep the join date from a
	 * previous membership of the same type.
	 *
	 * @since 0.4.4
	 * @param array $form Form configuration
	 */
	function preserve_membership_join_date( $form ) {

		$transient = $this->plugin->transient->get();

		if ( Caldera_Forms::get_processor_by_type( 'civicrm_membership', $form ) ) {
			foreach ( $form['processors'] as $id => $processor ) {
				if ( $processor['type'] == 'civicrm_membership' && isset( $processor['config']['preserve_join_date'] ) ) {
					// associated memberships
					$associated_memberships = $this->plugin->helper->get_organization_membership_types( $processor['config']['member_of_contact_id'] );

					// add expired and cancelled
					add_filter( 'cfc_current_membership_get_status', [ $this, 'add_expired_status' ], 10 );
					if ( isset( $processor['config']['is_membership_type'] ) ) {
						// get oldest membersip
						$oldest_membership = $this->plugin->helper->get_membership(
							$transient->contacts->{$this->contact_link},
							$transient->memberships->$id->params['membership_type_id'],
							'ASC'
						);
					} else {
						$oldest_membership = $this->plugin->helper->get_membership(
							$transient->contacts->{$this->contact_link},
							$membership_type = false,
							$sort = 'ASC'
						);
					}
					// remove filter
					remove_filter( 'cfc_current_membership_get_status', [ $this, 'add_expired_status' ], 10 );

					if ( $this->is_pay_later ) {
						// is pay later, filter membership status to pending
						add_filter( 'cfc_current_membership_get_status', [ $this, 'set_pending_status' ], 10 );
						// get latest membership
						if ( $oldest_membership )
							$latest_membership = $this->plugin->helper->get_membership(
								$transient->contacts->{$this->contact_link},
								$transient->memberships->$id->params['membership_type_id']
							);
						// remove filter
						remove_filter( 'cfc_current_membership_get_status', [ $this, 'set_pending_status' ], 10 );
					} else {
						if ( $oldest_membership )
							$latest_membership = $this->plugin->helper->get_membership(
								$transient->contacts->{$this->contact_link},
								$transient->memberships->$id->params['membership_type_id'],
								$sort = 'DESC',
								$skip_status = true
							);
					}

					if ( $latest_membership && date( 'Y-m-d', strtotime( $oldest_membership['join_date'] ) ) < date( 'Y-m-d', strtotime( $latest_membership['join_date'] ) ) ) {
						// is latest/current membership one of associated?
						if ( $associated_memberships && in_array( $latest_membership['membership_type_id'], $associated_memberships ) ) {
							// set oldest join date
							$latest_membership['join_date'] = $oldest_membership['join_date'];
							// update membership
							$update_membership = civicrm_api3( 'Membership', 'create', $latest_membership );
						}
					}

					unset( $latest_membership, $oldest_membership, $associated_memberships );
				}
			}
		}

	}

	/**
	 * Set Pending status.
	 *
	 * @uses 'cfc_current_membership_get_status' filter
	 * @since 0.4.4
	 * @param array $statuses Membership statuses array
	 */
	public function set_pending_status( $statuses ) {
		return [ 'Pending' ];
	}

	/**
	 * Add expired and cancelled statuses.
	 *
	 * @uses 'cfc_current_membership_get_status' filter
	 * @since 0.4.4
	 * @param array $statuses Membership statuses array
	 */
	public function add_expired_status( $statuses ) {
		return array_merge( $statuses, [ 'Expired', 'Cancelled' ] );
	}

	/**
	 * Add payment processor hooks before pre process starts.
	 *
	 * @since 0.4.4
	 *
	 * @param array $form Form config
	 * @param array $referrer URL referrer
	 * @param string $process_id The process id
	 */
	public function add_payment_processor_hooks( $form, $referrer, $process_id ) {

		// authorize single
		if ( Caldera_Forms::get_processor_by_type( 'auth-net-single', $form ) && ( Caldera_Forms_Field_Util::has_field_type( 'civicrm_country', $form ) || Caldera_Forms_Field_Util::has_field_type( 'civicrm_state', $form ) ) ) {

			/**
			 * Filter Authorize single payment customer data.
			 *
			 * @since 0.4.4
			 *
			 * @param object $customer Customer data
			 * @param string $prefix processor slug prefix
			 * @param object $data_object Processor data object
			 * @return object $customer Customer data
			 */
			add_filter( 'cf_authorize_net_setup_customer', function( $customer, $prefix, $data_object ) use ( $form ) {

				foreach ( $data_object->get_fields() as $name => $field ) {
					if ( $name == $prefix . 'card_state' || $name == $prefix . 'card_country' ) {
						if ( ! empty( $field['config_field'] ) ) {
							// get field config
							$field_config = Caldera_Forms_Field_Util::get_field( $field['config_field'], $form );

							// replace country id with label
							if ( $field_config['type'] == 'civicrm_country' )
								$customer->country = $this->plugin->fields->field_objects['civicrm_country']->field_render_view( $customer->country, $field_config, $form );
							// replace state id with label
							if ( $field_config['type'] == 'civicrm_state' )
								$customer->state = $this->plugin->fields->field_objects['civicrm_state']->field_render_view( $customer->state, $field_config, $form );
						}
					}
				}
				return $customer;
			}, 10, 3 );
		}

		// stripe
		if ( Caldera_Forms::get_processor_by_type( 'stripe', $form ) ) {
			/**
			 * Process the Stripe balance transaction to get the fee and card detials.
			 *
			 * @since  0.4.4
			 *
			 * @param array $return_charge Data about the successful charge
			 * @param array $transdata Data used to create transaction
			 * @param array $config The proessor config
			 * @param array $form The form config
			 */
			add_action( 'cf_stripe_post_successful_charge', function( $return_charge, $transdata, $config, $stripe_form ) {
				// stripe charge object from the successful payment
				$balance_transaction_id = $transdata['stripe']->balance_transaction;

				\Stripe\Stripe::setApiKey( $config['secret'] );
				$balance_transaction_object = \Stripe\BalanceTransaction::retrieve( $balance_transaction_id );

				$charge_metadata = [
					'fee_amount' => $balance_transaction_object->fee / 100,
					'card_type_id' => $this->get_option_by_label( $transdata['stripe']->source->brand ),
					'credit_card_type' => $transdata['stripe']->source->brand,
					'pan_truncation' => $transdata['stripe']->source->last4,
					'credit_card_exp_date' => [
						'M' => $transdata['stripe']->source->exp_month,
						'Y' => $transdata['stripe']->source->exp_year
					]
				];

				$this->charge_metadata = $charge_metadata;
			}, 10, 4 );
		}
	}

	/**
	 * Create premium.
	 *
	 * @since 1.0
	 * @param array $order The order/contribution
	 * @param array $form_values The submitted form values
	 * @param array $config The processor config
	 */
	public function create_premium( $order, $form_values, $config ) {

		global $transdata;

		if ( ! isset( $form_values['product_id'] ) ) return;

		if ( ! $order ) return;

		$params = [
			'product_id' => $form_values['product_id'],
			'contribution_id' => $order['id'],
			'quantity' => 1 // FIXME, can this be set via UI?
		];

		if ( isset( $transdata['data'][$config['product_id'] . '_option'] ) )
			$params['product_option'] = $transdata['data'][$config['product_id'] . '_option'];

		try {
			$premium = civicrm_api3( 'ContributionProduct', 'create', $params );
		} catch ( CiviCRM_API3_Exception $e ) {
			// log error
		}
	}

	/**
	 * Track CiviDiscounts.
	 *
	 * @since 1.0
	 * @param array $order The order with it's line items
	 */
	public function track_cividiscounts( $order ) {

		if ( ! $order || ! isset( $order['id'] ) ) return;

		if ( ! isset( $this->plugin->cividiscount ) ) return;

		if ( empty( $this->plugin->processors->processors['participant']->discounts_used ) ) return;

		if ( empty( $this->plugin->processors->processors['participant']->price_field_refs ) || empty( $this->plugin->processors->processors['participant']->price_field_option_refs ) ) return;

		$price_field_refs = $this->plugin->processors->processors['participant']->price_field_refs;
		$price_field_option_refs = $this->plugin->processors->processors['participant']->price_field_option_refs;
		$discounts_used = $this->plugin->processors->processors['participant']->discounts_used;

		$price_field_option_refs = array_reduce( $price_field_option_refs, function( $refs, $ref ) {
			$refs[$ref['processor_id']] = $ref['field_id'];
			return $refs;
		}, [] );

		$participant_ids = array_reduce( $order['line_items'], function( $ids, $item ) {
			if ( $item['entity_table'] == 'civicrm_participant' )
				$ids[] = $item['entity_id'];

			return $ids;
		}, [] );

		$participant_items = array_reduce( $order['line_items'], function( $items, $item ) {
			if ( $item['entity_table'] == 'civicrm_participant' )
				$items[$item['entity_id']] = $item;

			return $items;
		}, [] );

		$participants = civicrm_api3( 'Participant', 'get', [
			'id' => [ 'IN' => $participant_ids ],
			'options' => [ 'limit' => 0 ]
		] );

		if ( $participants['is_error'] && ! $participants['count'] ) return;

		$participants = array_reduce( $participants['values'], function( $participants, $participant ) {
			$participants[] = $participant;
			return $participants;
		}, [] );

		$refs = array_merge( $price_field_refs, $price_field_option_refs );

		$transient = $this->plugin->transient->get();

		array_map( function( $processor_id, $field_id ) use ( $discounts_used, $transient, $order, $participants, $participant_items ) {

			$discount = isset( $discounts_used[$field_id] ) ? $discounts_used[$field_id] : false;

			if ( ! $discount ) return;

			$processor_id = $this->plugin->helper->parse_processor_id( $processor_id );

			$event_id = $transient->events->$processor_id->event_id;

			$participant = array_filter( $participants, function( $participant ) use ( $event_id ) {
				return $participant['event_id'] == $event_id;
			} );

			$participant = array_pop( $participant );

			if ( ! $participant ) return;

			try {
				$discount_track = civicrm_api3( 'DiscountTrack', 'create', [
					'item_id' => $discount['id'],
					'contact_id' => $order['contact_id'],
					'contribution_id' => $order['id'],
					'entity_table' => $participant_items[$participant['id']]['entity_table'],
					'entity_id' => $participant['id'],
					'description' => [ $participant_items[$participant['id']]['label'] ]
				] );
			} catch ( CiviCRM_API3_Exception $e ) {
				Civi::log()->debug( 'Unable to track discount ' . $discount['code'] . ' for contribution id ' . $order['id'] );
			}

		}, array_keys( $refs ), $refs );

	}

	/**
	 * Order has participants.
	 *
	 * @since 1.0.1
	 * @param array $form_values The submitted values
	 * @return bool $has_participant
	 */
	public function has_participant_item( $line_items ) {

		if ( ! is_array( $line_items ) || empty( $line_items ) ) return false;

		$participant_line_items = array_filter( $line_items, function( $item ) {
			return $item['line_item'][0]['entity_table'] === 'civicrm_participant';
		} );

		return ! empty( $participant_line_items );
	}

	/**
	 * Send email confirmation/receipt.
	 *
	 * @since 0.4.4
	 *
	 * @param array $order The Order api result
	 * @param array $config Processor config
	 */
	public function maybe_send_confirmation( $order, $config ) {

		if ( ! $order ) return;

		if ( isset( $order['id'] ) && isset( $config['is_email_receipt'] ) ) {
			try {
				civicrm_api3( 'Contribution', 'sendconfirmation', [ 'id' => $order['id'] ] );
			} catch ( CiviCRM_API3_Exception $e ) {
				Civi::log()->debug( 'Unable to send confirmation email for Contribution id ' . $order['id'] );
			}
		}
	}

	/**
	 * Get OptionValue by label.
	 *
	 * @since 0.4.4
	 *
	 * @param string $label
	 * @return mixed $value
	 */
	public function get_option_by_label( $label ) {
		try {
			$option_value = civicrm_api3( 'OptionValue', 'getsingle', [
				'label' => $label,
			] );

		} catch ( CiviCRM_API3_Exception $e ) {
			// ignore
		}

		if ( isset( $option_value ) && is_array( $option_value ) )
			return $option_value['value'];
		return null;
	}
}
