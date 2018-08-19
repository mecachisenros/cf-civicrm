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

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Order', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM Order (Contribution with multiple Line Items, ie Events registrations, Donations, Memberships, etc.)', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/order/order_config.php',
			'single' => true,
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'post_processor' => [ $this, 'post_processor'],
		);

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

		$config_line_items = $config['line_items'];
		unset( $config['line_items'] );
		
		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		$form_values['financial_type_id'] = $config['financial_type_id'];
		$form_values['contribution_status_id'] = $config['contribution_status_id'];
		$form_values['payment_instrument_id'] = ! isset( $config['is_mapped_field'] ) ?
			$config['payment_instrument_id'] :
			$form_values['mapped_payment_instrument_id'];
		
		$form_values['currency'] = $config['currency'];

		// $form_values['receipt_date'] = date( 'YmdHis' );
		
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
		$line_items = [];
		$count = 0;
		foreach ( $config_line_items as $item => $processor ) {
			if( ! empty( $processor ) ) {
				$processor = Caldera_Forms::do_magic_tags( $processor );
				if ( ! strpos( $processor, 'civicrm_line_item' ) ) {
					$line_items[$count] = $transient->line_items->$processor->params;
					if ( 
						isset( $line_items[$count]['params']['membership_type_id'] ) && $this->is_pay_later ) {
							// set membership as pending
							$line_items[$count]['params']['status_id'] = 'Pending';
							$line_items[$count]['params']['is_override'] = 1;
					}
				}
				$count++;
			} else {
				unset( $config_line_items[$item] );
			}
		}

		$form_values['line_items'] = $line_items;

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

		try {
			$create_order = civicrm_api3( 'Order', 'create', $form_values );
		} catch ( CiviCRM_API3_Exception $e ) {
			$transdata['error'] = true;
			$transdata['note'] = $e->getMessage() . '<br><br><pre' . $e->getTraceAsString() . '</pre>';
		}
		if( ! $create_order['is_error'] && isset( $create_order['id'] ) && $config['is_email_receipt'] )
			civicrm_api3( 'Contribution', 'sendconfirmation', [ 'id' => $create_order['id'] ] );

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
		$this->preserve_membership_join_date( $config, $form, $processid );

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
				$template_path = apply_filters( 'cfc_order_thank_you_template_path', CF_CIVICRM_INTEGRATION_PATH . 'template/thank-you.php', $_form );

				$form_values = Caldera_Forms::get_submission_data( $_form );

				$data = [
					'values' => $form_values,
					'form' => $_form,
					'transdata' => $transdata,
					'transient' => $transient
				];

				$html = $this->plugin->html->generate( $data, $template_path, $this->plugin );

				$out['html'] = $out['html'] . $html;

				return $out;

			}, 10, 2 );
		}

	}

	/**
	 * Preserve join date for current membership being processed.
	 *
	 * Background, implemented for new memberships considered as renewals to keep the join date from a
	 * previous membership of the same type.
	 *
	 * @since 0.4.4
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	function preserve_membership_join_date( $config, $form, $processid ) {
		
		$transient = $this->plugin->transient->get();
		
		if ( Caldera_Forms::get_processor_by_type( 'civicrm_membership', $form ) ) {
			// associated memberships
			$associated_memberships = $this->plugin->helper->get_organization_membership_types( $processor['config']['member_of_contact_id'] );
			foreach ( $form['processors'] as $id => $processor ) {
				if ( $processor['type'] == 'civicrm_membership' && isset( $processor['config']['preserve_join_date'] ) ) {

					if ( isset( $processor['config']['is_membership_type'] ) ) {
						// add expired
						add_filter( 'cfc_current_membership_get_status', [ $this, 'add_expired_status' ], 10 );
						// get oldest membersip
						$oldest_membership = $this->plugin->helper->get_membership( 
							$transient->contacts->{$this->contact_link},
							$transient->memberships->$id->params['membership_type_id'],
							'ASC'
						);
						// remove filter
						remove_filter( 'cfc_current_membership_get_status', [ $this, 'add_expired_status' ], 10 );
					} else {
						$oldest_membership = $this->plugin->helper->get_membership( 
							$transient->contacts->{$this->contact_link},
							false,
							'ASC'
						);
					}

					// is pay later, filter membership status to pending 
					if ( $this->is_pay_later )
						add_filter( 'cfc_current_membership_get_status', [ $this, 'set_pending_status' ], 10 );

					// get latest membership
					if ( $oldest_membership )
						$latest_membership = $this->plugin->helper->get_membership( 
							$transient->contacts->{$this->contact_link},
							$transient->memberships->$id->params['membership_type_id']
						);

					// remove filter
					remove_filter( 'cfc_current_membership_get_status', [ $this, 'set_pending_status' ], 10 );
					
					if ( $latest_membership && date( 'Y-m-d', strtotime( $oldest_membership['join_date'] ) ) < date( 'Y-m-d', strtotime( $latest_membership['join_date'] ) ) ) {
						// is latest/current membership one of associated?
						if ( $associated_memberships && in_array( $latest_membership['membership_type_id'], $associated_memberships ) ) {
							// set oldest join date
							$latest_membership['join_date'] = $oldest_membership['join_date'];
							// update membership
							$update_membership = civicrm_api3( 'Membership', 'create', $latest_membership );
						}
					}

					unset( $latest_membership, $oldest_membership );
				}
			}
		}

		return $form;
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
	 * Add expired status.
	 *
	 * @uses 'cfc_current_membership_get_status' filter
	 * @since 0.4.4
	 * @param array $statuses Membership statuses array
	 */
	public function add_expired_status( $statuses ) {
		$statuses[] = 'Expired';
		return $statuses;
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
