<?php

/**
 * CiviCRM Caldera Forms Order Processor Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Order2_Processor {

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
	 * The processor key.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_order2';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
        $this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );
		// stripe successfull payment
		add_action( 'cf_stripe_post_successful_charge', [ $this, 'stripe_charge_metadata' ], 10, 4 );

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
			'name' => __( 'CiviCRM Order Alt', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM Order (Contribution with multiple Line Items, ie Events registrations, Donations, Memberships, etc.)', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/order2/order_config.php',
			'single' => true,
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function pre_processor( $config, $form, $processid ) {
		
	}

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
						isset( $line_items[$count]['params']['membership_type_id'] ) && 
						isset( $config['is_pay_later'] ) && 
						in_array( $form_values['payment_instrument_id'], [$config['is_pay_later']] ) ) {
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
	 * Process the Stripe balance transaction to get the fee and card detials.
	 *
	 * @since  0.4.4
	 * 
	 * @param array $return_charge Data about the successful charge
	 * @param array $transdata Data used to create transaction
	 * @param array $config The proessor config
	 * @param array $form The form config
	 */
	public function stripe_charge_metadata( $return_charge, $transdata, $config, $form ) {
		
		// stripe charge object from the successful payment
		$balance_transaction_id = $transdata['stripe']->balance_transaction;
		
		\Stripe\Stripe::setApiKey( $config['secret'] );
		$balance_transaction_object = \Stripe\BalanceTransaction::retrieve( $balance_transaction_id );
		
		$charge_metadata = [
			'fee_amount' => $balance_transaction_object->fee / 100,
			'card_type_id' => $this->get_option_by_label( $transdata['stripe']->source->brand ),
			'pan_truncation' => $transdata['stripe']->source->last4,
			'credit_card_exp_date' => [
				'M' => $transdata['stripe']->source->exp_month,
				'Y' => $transdata['stripe']->source->exp_year
			]
		];

		$this->charge_metadata = $charge_metadata;
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
