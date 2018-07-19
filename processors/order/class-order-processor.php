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
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

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
			'description' => __( 'Add CiviCRM Order (Contribution with multiple Line Items, ie Events registrations, Donations, etc.)', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/order/order_config.php',
			'pre_processor' =>  array( $this, 'processor' ),
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
	public function processor( $config, $form, $processid ) {
		
		// globalised transient object
		global $transdata;

		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];
		
		$line_items = $config['line_item'];
		unset( $config['line_item'] );
		
		foreach ( $line_items as $key => $line_item ) {
			if ( empty( $line_item['price_field_value'] ) && empty( $line_item['entity_table'] ) ) {
				unset( $line_items[$key] );
				continue;
			}
			$line_items[$key] = $line_item;

		}

		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		$form_values['financial_type_id'] = $config['financial_type_id'];
		$form_values['contribution_status_id'] = $config['contribution_status_id'];

		$form_values['receipt_date'] = date('YmdHis');
		
		if( ! isset( $form_values['source'] ) )
			$form_values['source'] = $form['name'];
		
		$form_values['contact_id'] = $transient->contacts->{$this->contact_link};
		$form_values['line_items'] = $this->build_line_item_data( $line_items, $form );


		
		try {
			$create_order = civicrm_api3( 'Order', 'create', $form_values );
		} catch ( CiviCRM_API3_Exception $e ) {
			$error = $e->getMessage() . '<br><br><pre' . $e->getTraceAsString() . '</pre>';
			return array( 'note' => $error, 'type' => 'error' );
		}
	}

	/**
	 * Build line items.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @return array $line_items The line items
	 */
	public function build_line_item_data( $items, $form ) {
		// global $transdata;
		$transient = $this->plugin->transient->get();

		$line_items = array();
		foreach ( $items as $key => $item ) {
			$item['price_field_value'] = isset( $item['is_fixed_price_field'] ) ? $item['fixed_price_field_value'] : $item['price_field_value']; 
			if( ! empty( $item['price_field_value'] ) ) {
				// get data from binded field
				if ( isset( $item['is_fixed_price_field'] ) ) {
					$line_items[$key]['price_field_value'] = $item['price_field_value'];
				} else {
					$line_items[$key]['price_field_value'] = Caldera_Forms::get_field_data( $item['price_field_value'], $form );
					$line_items[$key]['price_field_value'] = Caldera_Forms::get_field_by_slug( str_replace( '%', '', $item['price_field_value'] ), $form );
				}

				try{
					$price_field_value = civicrm_api3( 'PriceFieldValue', 'getsingle', array(
						'sequential' => 1,
						'id' => $line_items[$key]['price_field_value'],
					));
				} catch( CiviCRM_API3_Exception $e ){
					$error = $e->getMessage() . '<br><br><pre' . $e->getTraceAsString() . '</pre>';
					return array( 'note' => $error, 'type' => 'error' );
				}
				
				$line_items[$key]['line_item'][] = array(
					'price_field_id' => $price_field_value['price_field_id'],
		            'price_field_value_id' => $price_field_value['id'],
		            'label' => $price_field_value['label'],
		            'field_title' => $price_field_value['label'],
		            'qty' => $item['qty'] ? $item['qty'] : 1,
		            'unit_price' => $price_field_value['amount'],
		            'line_total' => $item['qty'] ? $item['qty'] * $price_field_value['amount'] : $price_field_value['amount'],
		            'financial_type_id' => $price_field_value['financial_type_id'],
		            'entity_table' => $item['entity_table'],
				);

				// $line_items[$key]['params'] = $transdata['civicrm']['participants']['participant_'.$item['contact_link']];
				$line_items[$key]['params'] = $transient->memberships->{'mid_'.$this->contact_link};
				
				// if ( $item['entity_table'] != 'civicrm_participant' )
					// unset( $line_items[$key]['params'] );
			}
		}
		
		return $line_items;
	}
}
