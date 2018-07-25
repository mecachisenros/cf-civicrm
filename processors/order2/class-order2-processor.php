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
	protected $fee = false;

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
		add_action( 'cf_stripe_post_successful_charge', [ $this, 'get_balance_transaction' ], 10, 4 );

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
		$form_values['payment_instrument_id'] = $config['payment_instrument_id'];
		$form_values['currency'] = $config['currency'];

		$form_values['receipt_date'] = date('YmdHis');
		
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
				if ( ! strpos( $processor, 'civicrm_line_item' ) )
					$line_items[$count] = $transient->line_items->$processor->params;
				$count++;
			} else {
				unset( $config_line_items[$item] );
			}
		}

		// check if the membership is the same type
		$total_num_terms = '';
		foreach ( $line_items as $key => $item ) {

		}

		$form_values['line_items'] = $line_items;

		// payment processor fee
		if ( $this->fee ) $form_values['fee_amount'] = $this->fee / 100;

		try {
			$create_order = civicrm_api3( 'Order', 'create', $form_values );
		} catch ( CiviCRM_API3_Exception $e ) {
			$transdata['error'] = true;
			$transdata['note'] = $e->getMessage() . '<br><br><pre' . $e->getTraceAsString() . '</pre>';
		}

	}

	/**
	 * Process the Stripe balance transaction to get the fee.
	 *
	 * @since  0.4.4
	 * 
	 * @param array $return_charge Data about the successful charge
	 * @param array $transdata Data used to create transaction
	 * @param array $config The proessor config
	 * @param array $form The form config
	 */
	public function get_balance_transaction( $return_charge, $transdata, $config, $form ) {
		
		// stripe charge object from the successful payment
		$balance_transaction_id = $transdata['stripe']->balance_transaction;
		
		\Stripe\Stripe::setApiKey( $config['secret'] );
		$balance_transaction_object = \Stripe\BalanceTransaction::retrieve( $balance_transaction_id );

		$this->fee = $balance_transaction_object->fee;
	}
}
