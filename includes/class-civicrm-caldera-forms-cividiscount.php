<?php

/**
 * CiviCRM Caldera Forms CiviDiscount Class.
 *
 * @since 1.0
 */
class CiviCRM_Caldera_Forms_CiviDiscount {

	/**
	 * Plugin reference.
	 *
	 * @since 1.0
	 */
	public $plugin;

	/**
	 * Event CiviDisounts by processor.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $event_cividiscounts Reference to [ <processor_id> => <discount> ]
	 */
	public $event_cividiscounts;

	/**
	 * CiviDiscount criteria/filters,
	 * whether the criteria is met for a processor_id.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $event_autodiscounts Reference to [ <processor_id> => true|false ]
	 */
	public $event_autodiscounts;

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get CiviDiscounts for events.
	 *
	 * @since 1.0
	 * @param array $event_ids The event ids to get discounts for
	 * @return array $event_cividiscounts Discounts per processors array
	 */
	public function get_event_cividiscounts( $event_ids ) {

		if ( is_array( $this->event_cividiscounts ) ) return $this->event_cividiscounts;

		$discount = civicrm_api3( 'DiscountCode', 'get', [
			'events' => [ 'IN' => array_values( $event_ids ) ],
			'is_active' => 1
		] );

		$event_cividiscounts = array_reduce( $discount['values'], function( $discounts, $discount ) use ( $event_ids ) {
			$processor_id = array_search( array_pop( $discount['events'] ), $event_ids );
			$discount['autodiscount'] = json_decode( $discount['autodiscount'], true );

			if ( $processor_id )
				$discounts[$processor_id] = $discount;

			return $discounts;

		}, [] );

		$this->event_cividiscounts = $event_cividiscounts;

		return $this->event_cividiscounts;

	}

	/**
	 * Check autodiscount for a contact.
	 *
	 * @since 1.0
	 * @param array $autodiscount CiviDiscount autodiscount (criteria/filter) property
	 * @param int $contact_id The contact id
	 * @param string $processor_id The praticipant processor id
	 * @return bool $is_autodiscount
	 */
	public function check_autodiscount( $autodiscount, $contact_id, $processor_id ) {

		if ( isset( $this->event_autodiscounts[$processor_id] ) ) return $this->event_autodiscounts[$processor_id];

		$is_autodiscount = false;

		if ( ! empty( $autodiscount ) ) {
			foreach ( $autodiscount as $entity => $params ) {
				$params['contact_id'] = $contact_id;
				try {
					$result = civicrm_api3( $entity, 'getsingle', $params );
					if ( $result['count'] || isset( $result['id'] ) ) {
						$is_autodiscount = true;
						break;
					}
				} catch ( CiviCRM_API3_Exception $e ) {
					
				}
			}
		}
		$this->event_autodiscounts[$processor_id] = $is_autodiscount;

		return $is_autodiscount;
	}

	public function get_by_code( $code ) {

		try {
			$discount = civicrm_api3( 'DiscountCode', 'getsingle', [
				'sequential' => 1,
				'code' => $code,
				'is_active' => 1
			] );
		} catch ( CiviCRM_API3_Exception $e ) {

		}

		if ( $discount && ! $discount['is_error'] )
			return $discount;

		return false;
	}

	public function do_discounted_option( $option, $field, $price_field_value, $event_discount ) {

		$label = sprintf( __( '%1$s (Includes automatic discount of: ', 'caldera-forms-civicrm' ), $price_field_value['label'] );

		// percentage discount
		if ( $event_discount['amount_type'] == 1 ) {
			$discounted_amount = $price_field_value['amount'] - $this->plugin->helper->calculate_percentage( $price_field_value['amount'], $event_discount['amount'] );
			$label .= $event_discount['amount'] . __( '%)', 'caldera-forms-civicrm' );
		}
		// fixed discount
		if ( $event_discount['amount_type'] == 2 ) {
			$discounted_amount = $price_field_value['amount'] - $event_discount['amount'];
			$label .= $this->plugin->helper->format_money( $event_discount['amount'] ) . __( ')', 'caldera-forms-civicrm' );
		}
		// filtered option
		$option = [
			'value' => $price_field_value['id'],
			'label' => sprintf( '%1$s - %2$s', $label, $this->plugin->helper->format_money( $discounted_amount ) ),
			'calc_value' => $discounted_amount,
			'disabled' => $option['disabled']
		];

		add_filter( 'cfc_filter_price_field_value_get', function( $field_value, $field_value_id ) use ( $price_field_value, $discounted_amount, $label ) {
			if ( $field_value_id == $price_field_value['id'] ) {
				$field_value['amount'] = $discounted_amount;
				$field_value['label'] = $label;
			}

			return $field_value;

		}, 10, 2 );

		// has tax
		if ( $price_field_value['tax_amount'] && $this->plugin->helper->get_tax_settings()['invoicing'] ) {
			$option['calc_value'] += $price_field_value['tax_amount'];
			$option['label'] = $this->plugin->helper->format_tax_label( $label, $discounted_amount, $price_field_value['tax_amount'] );
		}

		return $option;
	}

	public function do_code_event_discount_options( $discount, $form ) {

		if ( ! $discount ) return;

		// participant processors
		$participants = $this->plugin->helper->get_processor_by_type( 'civicrm_participant', $form );
		// price field references
		$price_field_refs = $this->plugin->processors->processors['participant']->build_price_field_refs( $form );
		// field configs
		$fields = array_reduce( $price_field_refs, function( $fields, $field_id ) use ( $form ) {
			$fields[$field_id] = Caldera_Forms_Field_Util::get_field( $field_id, $form, $apply_filters = true );
			return $fields;
		}, [] );

		return array_reduce( $price_field_refs, function( $discounted_options, $field_id ) use ( $fields, $form, $participants, $discount, $price_field_refs ) {

			$processor_id = array_search( $field_id, $price_field_refs );

			if ( ! in_array( $participants[$processor_id]['config']['id'], $discount['events'] ) ) return $discounted_options;

			$field = $fields[$field_id];

			$price_field = $this->plugin->fields->presets_objects['civicrm_price_sets']->get_price_field_from_config( $field );

			// $field = $this->plugin->processors->processors['participant']->do_event_autodiscounts( $field, $form, $processor_id, $price_field );

			$field['config']['option'] = array_reduce( $price_field['price_field_values'], function( $options, $price_field_value ) use ( $field, $discount ) {

				$option = $field['config']['option'][$price_field_value['id']];

				// do discounted option
				$options[$price_field_value['id']] = $this->do_discounted_option( $option, $field, $price_field_value, $discount );

				return $options;

			}, [] );

			$options = array_reduce( $field['config']['option'], function( $options, $option ) use ( $field_id, $field, $form ) {
				$field_option_id = $field_id . '_' . $form['form_count'] . '_' . $option['value'];
				$options[$field_option_id] = $field['config']['option'][$option['value']];
				$options[$field_option_id]['field_id'] = $field_id; 
				return $options;
			}, [] );

			return $discounted_options + $options;

		}, [] );
	}

	public function get_discount_fields( $form ) {
		return array_filter( $form['fields'], function( $field ) {
			return $field['type'] === 'civicrm_discount';
		} );
	}

}
