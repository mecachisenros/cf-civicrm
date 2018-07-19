<?php

/**
 * CiviCRM Caldera Forms Line Item Processor Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Line_Item_Processor {

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
	public $key_name = 'civicrm_line_item';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct($plugin) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );

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
			'name' => __( 'CiviCRM Line Item', 'caldera-forms-civicrm' ),
			'description' => __( 'Add Line Item for the Order Processor', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/line-item/line_item_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'magic_tags' => [ 'processor_id' ],
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

		// globalised transient object
		global $transdata;
		$this->plugin->slack( $form['processors'][$config['processor_id']] );

        // $transient->line_items->$config['processor_id'] = 

		// $line_items = $config['line_item'];
		// unset( $config['line_item'] );

		// foreach ( $line_items as $key => $line_item ) {
		// 	if ( empty( $line_item['price_field_value'] ) && empty( $line_item['entity_table'] ) ) {
		// 		unset( $line_items[$key] );
		// 		continue;
		// 	}
		// 	$line_items[$key] = $line_item;

		// }

		// Get form values
		// $form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );

		// $form_values['financial_type_id'] = $config['financial_type_id'];
		// $form_values['contribution_status_id'] = $config['contribution_status_id'];

		// $form_values['receipt_date'] = date('YmdHis');

		// if( ! isset( $form_values['source'] ) )
		// 	$form_values['source'] = $form['name'];

		// $form_values['contact_id'] = $transient->contacts->{$this->contact_link};
		// $form_values['line_items'] = $this->build_line_item_data( $line_items, $form );
		// unset( $form_values['line_item'] );

		// try {
		// 	$create_order = civicrm_api3( 'Order', 'create', $form_values );
		// } catch ( CiviCRM_API3_Exception $e ) {
		// 	$error = $e->getMessage() . '<br><br><pre' . $e->getTraceAsString() . '</pre>';
		// 	return array( 'note' => $error, 'type' => 'error' );
		// }
	}

	public function processor( $config, $form, $processid ) {

		global $transdata;

		$transient = $this->plugin->transient->get();
		
		$this->contact_link = 'cid_' . $config['contact_link'];
        
		// price field value params aka 'line_item'
		$price_field_value = isset( $config['is_fixed_price_field'] ) ? 
			$this->plugin->helper->get_price_field_value( $config['fixed_price_field_value'] ) :
			$this->plugin->helper->get_price_field_value( Caldera_Forms::do_magic_tags( $config['price_field_value'] ) );
		
		$num_terms = $price_field_value['membership_num_terms'];
		$price_field_value['price_field_value_id'] = $price_field_value['id'];

		$price_field_value['entity_table'] = $config['entity_table'];
		$price_field_value['field_title'] = $price_field_value['label'];
		$price_field_value['unit_price'] = $price_field_value['amount'];
		$price_field_value['qty'] = 1;
		$price_field_value['line_total'] = $price_field_value['amount'] * $price_field_value['qty'];
		
		unset( $price_field_value['membership_num_terms'], $price_field_value['contribution_type_id'], $price_field_value['id'], $price_field_value['amount'] );
			
		// membership/participant params aka 'params'
		$processor_id = Caldera_Forms::do_magic_tags( $config['entity_params'] );
		if ( isset( $transient->memberships->$processor_id->params ) ) {
			$entity_params = $transient->memberships->$processor_id->params;
			$entity_params['num_terms'] = $num_terms;
			$entity_params['source'] = 'Testing memberships';
			if( isset( $price_field_value['membership_type_id'] ) )
				$entity_params['membership_type_id'] = $price_field_value['membership_type_id'];
		}

        $line_item = [ 
        	'line_item' => [ $price_field_value ],
        	'params' => $entity_params
        ];

        $transient->line_items->{$config['processor_id']}->params = $line_item;
        
        $this->plugin->transient->save( $transient->ID, $transient );

		return ['processor_id' => $config['processor_id']];
	}
}
