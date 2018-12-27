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
	 * CiviDiscounts.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $cividiscounts
	 */
	public $cividiscounts;

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
	 * CiviDiscount criteria/filters,
	 * whether the criteria is met for a processor_id.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $event_autodiscounts Reference to [ <processor_id> => true|false ]
	 */
	public $options_ids_refs;

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get CiviDiscounts.
	 *
	 * @since 1.0
	 * @return array|bool $cividiscounts
	 */
	public function get_cividiscounts() {

		if ( is_array( $this->cividiscounts ) ) return $this->cividiscounts;

		$discounts = civicrm_api3( 'DiscountCode', 'get', [
			'is_active' => 1,
			'options' => [ 'limit' => 0 ]
		] );

		if ( $discounts['count'] && ! $discounts['is_error'] ) {
			$discounts = array_map( function( $discount ) {
				$discount['autodiscount'] = json_decode( $discount['autodiscount'], true );
				return $discount;
			}, $discounts['values'] );

			// filter discounts by date
			$discounts = array_filter( $discounts, [ $this, 'is_discount_active' ] );
			$this->cividiscounts = $discounts;
			return $this->cividiscounts;
		}

		return false;

	}

	/**
	 * Get cividiscounts by entity.
	 *
	 * @since 1.0
	 * @param string $entity_name The entity name, events, memberships, or pricesets
	 * @return array $cividiscounts
	 */
	public function get_cividiscounts_by_entity( $entity_name, $is_autodiscount = null ) {
		$discounts = $this->get_cividiscounts();
		return array_filter( $discounts, function( $discount ) use ( $entity_name, $is_autodiscount ) {
			if ( $is_autodiscount === true ) {
				return array_key_exists( $entity_name, $discount ) && ! empty( $discount['autodiscount'] );
			} elseif ( $is_autodiscount === false ) {
				return array_key_exists( $entity_name, $discount ) && empty( $discount['autodiscount'] );
			} else {
				return array_key_exists( $entity_name, $discount );
			}
		} );
	}

	/**
	 * Get CiviDiscounts for price field options.
	 *
	 * @since 1.0
	 * @param array $event_ids The event ids to get discounts for
	 * @return array $event_cividiscounts Discounts per processors array
	 */
	public function get_event_cividiscounts( $event_ids ) {

		if ( is_array( $this->event_cividiscounts ) && ! empty( $this->event_cividiscounts ) ) return $this->event_cividiscounts;

		$event_cividiscounts = $this->get_cividiscounts_by_entity( 'events' ); 

		$event_discounts = array_reduce( $event_ids, function( $discounts, $event_id ) use ( $event_ids, $event_cividiscounts ) {

			$processor_id = array_search( $event_id, $event_ids );

			foreach ( $event_cividiscounts as $discount_id => $discount ) {
				if ( in_array( $event_id, $discount['events'] ) ) {
					$discounts[$processor_id] = $discount;
				}
			}

			return $discounts;

		}, [] );

		$this->event_cividiscounts = $event_discounts;

		return $this->event_cividiscounts;

	}

	/**
	 * Build options ids for price fields.
	 * 
	 * @since 1.0
	 * @param array $form The form config
	 * @return array|boolean $options_ids_refs References to [ <processor_id> => <event_id> ], or false
	 */
	public function build_options_ids_refs( $price_field_refs, $form = false ) {

		$discounted_options = $this->get_cividiscounts_by_entity( 'pricesets' );

		$options_ids_refs = array_reduce( $price_field_refs, function( $refs, $field_id ) use ( $price_field_refs, $discounted_options, $form ) {

			$processor_id = array_search( $field_id, $price_field_refs );

			$processor_id = $this->plugin->processors->processors['participant']->parse_processor_id( $processor_id );

			$price_field_field = Caldera_Forms_Field_Util::get_field( $field_id, $form, $apply_filters = true );

			if ( ! $price_field_field || ! isset( $price_field_field['ID'] ) ) return $refs;

			foreach ( $discounted_options as $discount_id => $discount ) {
				if ( ! empty( array_intersect( $discount['pricesets'], array_keys( $price_field_field['config']['option'] ) ) ) )
					$refs[$field_id] = [
						'field_id' => $field_id,
						'processor_id' => $processor_id,
						'field_options' => array_intersect( $discount['pricesets'], array_keys( $price_field_field['config']['option'] ) )
					];
			}

			return $refs;

		}, [] );

		$this->options_ids_refs = $options_ids_refs;

		return $options_ids_refs;

	}

	/**
	 * Get option based CiviDiscounts.
	 *
	 * @since 1.0
	 * @param array $options_ids_refs Field id with references to the discunted options (price_field_value ids)
	 * @return array $options_cividiscounts The discounts
	 */
	public function get_options_cividiscounts( $options_ids_refs ) {

		if ( is_array( $this->options_cividiscounts ) && ! empty( $this->options_cividiscounts ) ) return $this->options_cividiscounts;

		$options_cividiscounts = $this->get_cividiscounts_by_entity( 'pricesets' );

		$discounts = [];
		array_map( function( $field_id, $options ) use ( &$discounts, $options_ids_refs, $options_cividiscounts ) {

			foreach ( $options_cividiscounts as $discount_id => $discount ) {
				if ( ! empty( array_intersect( $options['field_options'], $discount['pricesets'] ) ) ) {
					$discounts[$field_id] = $discount;
					break;
				}
			}

		}, array_keys( $options_ids_refs ), $options_ids_refs );

		$this->options_cividiscounts = $discounts;

		return $this->options_cividiscounts;

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


	/**
	 * Get discount by discount code.
	 *
	 * @since 1.0
	 * @param string $code The discount code
	 * @return array $discount The discount
	 */
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

	/**
	 * Do option discount.
	 *
	 * @since 1.0
	 * @param array $option The option config
	 * @param array $field The field config
	 * @param array $price_field_value The price field value
	 * @param array $event_discount The discount config
	 * @return array $option The filtered option config
	 */
	public function do_discounted_option( $option, $field, $price_field_value, $discount ) {

		$label = sprintf( __( '%1$s (Includes automatic discount of: ', 'caldera-forms-civicrm' ), $price_field_value['label'] );

		// percentage discount
		if ( $discount['amount_type'] == 1 ) {
			$discounted_amount = $price_field_value['amount'] - $this->plugin->helper->calculate_percentage( $price_field_value['amount'], $discount['amount'] );
			$label .= $discount['amount'] . __( '%)', 'caldera-forms-civicrm' );
		}
		// fixed discount
		if ( $discount['amount_type'] == 2 ) {
			$discounted_amount = $price_field_value['amount'] - $discount['amount'];
			$label .= $this->plugin->helper->format_money( $discount['amount'] ) . __( ')', 'caldera-forms-civicrm' );
		}
		// filtered option
		$option = [
			'value' => $price_field_value['id'],
			'label' => sprintf( '%1$s - %2$s', $label, $this->plugin->helper->format_money( $discounted_amount ) ),
			'calc_value' => $discounted_amount,
			'disabled' => $option['disabled']
		];

		add_filter( 'cfc_filter_price_field_value_get', function( $field_value, $field_value_id ) use ( $price_field_value, $discounted_amount, $label ) {
			if ( ! is_array( $field_value_id ) && $field_value_id == $price_field_value['id'] ) {
				$field_value['amount'] = $discounted_amount;
				$field_value['label'] = $label;
			} elseif ( is_array( $field_value_id ) ) {
				if ( in_array( $price_field_value['id'], $field_value_id ) ) {
					$field_value[$price_field_value['id']]['amount'] = $discounted_amount;
					$field_value[$price_field_value['id']]['label'] = $label;
				}
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

	/**
	 * Do code discounts.
	 *
	 * @since 1.0
	 * @param array $discount The discount config
	 * @param array $form The form config
	 * @return array $options The discounted field options
	 */
	public function do_code_discount( $discount, $form ) {

		if ( ! $discount ) return;

		$options = $this->do_code_event_discount_options( $discount, $form );
		$options = $this->do_code_options_discount_options( $discount, $form );

		return $options;
	}

	/**
	 * Do code discounts for event based CiviDiscounts.
	 *
	 * @since 1.0
	 * @param array $discount The discount config
	 * @param array $form The forms config
	 * @return array $options The field options
	 */
	public function do_code_event_discount_options( $discount, $form ) {

		if ( ! $discount ) return;

		// participant processors
		$participants = $this->plugin->helper->get_processor_by_type( 'civicrm_participant', $form );
		// price field references
		$price_field_refs = $this->plugin->processors->processors['participant']->build_price_field_refs( $form );
		// options fields refs
		$price_field_option_refs = $this->build_options_ids_refs( $price_field_refs, $form );
		// filter out option based discounts
		$discounted_fields = array_filter( $price_field_refs, function( $field_id ) use ( $price_field_option_refs ) {
			return ! array_key_exists( $field_id, $price_field_option_refs );
		} );
		// field configs
		$fields = array_reduce( $discounted_fields, function( $fields, $field_id ) use ( $form ) {
			$fields[$field_id] = Caldera_Forms_Field_Util::get_field( $field_id, $form, $apply_filters = true );
			return $fields;
		}, [] );

		return array_reduce( $discounted_fields, function( $discounted_options, $field_id ) use ( $fields, $form, $participants, $discount, $discounted_fields ) {

			$processor_id = array_search( $field_id, $discounted_fields );

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

	/**
	 * Do code discounts for options based CiviDiscounts.
	 *
	 * @since 1.0
	 * @param array $discount The discount config
	 * @param array $form The forms config
	 * @return array $options The field options
	 */
	public function do_code_options_discount_options( $discount, $form ) {

		if ( ! $discount ) return;

		// price field references
		$price_field_refs = $this->plugin->processors->processors['participant']->build_price_field_refs( $form );
		// options fields refs
		$price_field_option_refs = $this->build_options_ids_refs( $price_field_refs, $form );
		// field configs
		$fields = array_reduce( $price_field_option_refs, function( $fields, $ref ) use ( $form ) {
			$fields[$ref['field_id']] = Caldera_Forms_Field_Util::get_field( $ref['field_id'], $form, $apply_filters = true );
			return $fields;
		}, [] );

		return array_reduce( $price_field_option_refs, function( $discounted_options, $ref ) use ( $fields, $form, $discount, $discounted_fields ) {

			$field = $fields[$ref['field_id']];

			if ( empty( array_intersect( $discount['pricesets'], array_keys( $field['config']['option'] ) ) ) ) return $discounted_options;

			$price_field = $this->plugin->fields->presets_objects['civicrm_price_sets']->get_price_field_from_config( $field );

			$field['config']['option'] = array_reduce( $price_field['price_field_values'], function( $options, $price_field_value ) use ( $field, $discount ) {

				$option = $field['config']['option'][$price_field_value['id']];

				// do discounted option
				$options[$price_field_value['id']] = in_array( $price_field_value['id'], $discount['pricesets'] ) ?
					$this->do_discounted_option( $option, $field, $price_field_value, $discount ) :
					$option;

				return $options;

			}, [] );

			$options = array_reduce( $field['config']['option'], function( $options, $option ) use ( $ref, $field, $form ) {
				$field_option_id = $ref['field_id'] . '_' . $form['form_count'] . '_' . $option['value'];
				$options[$field_option_id] = $field['config']['option'][$option['value']];
				$options[$field_option_id]['field_id'] = $ref['field_id']; 
				return $options;
			}, [] );
			return $discounted_options + $options;

		}, [] );

	}

	/**
	 * Get fields of type 'civicrm_discount'.
	 *
	 * @since 1.0
	 * @param array $form The form config
	 * @return array $discount_fields Array holding the discount fields configs
	 */
	public function get_discount_fields( $form ) {
		return array_filter( $form['fields'], function( $field ) {
			return $field['type'] === 'civicrm_discount';
		} );
	}

	/**
	 * Check if discount is active based on active_on/expire_on.
	 *
	 * @since 1.0
	 * @param array $discount The discount
	 * @return boolean $is_active Whether is active or not
	 */
	public function is_discount_active( $discount ) {

		$now = date( 'Y-m-d H:m:i' );
		$active_on = isset( $discount['active_on'] ) && array_key_exists( 'active_on', $discount ) ? $discount['active_on'] : $now;
		$expire_on = isset( $discount['expire_on'] ) && array_key_exists( 'expire_on', $discount ) ? $discount['expire_on'] : $now;

		return ( $now >= $active_on ) && ( $now <= $expire_on );

	}

}
