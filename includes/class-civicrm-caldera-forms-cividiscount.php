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
	 * CiviDisounts by processor references, including
	 * memberships, events, and contributions.
	 *
	 * @since 1.0.5
	 * @access public
	 * @var array $entities_cividiscounts Reference to [ <processor_id> => <discount> ]
	 */
	public $entities_cividiscounts;

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
	 * Reference to the discounts used for tracking.
	 *
	 * @since 1.0.5
	 * @var array $discounts_used Reference to [ <processor_id> => <discount> ]
	 */
	public $discounts_used;

	/**
	 * CiviDiscount criteria/filters,
	 * whether the criteria is met for a processor_id.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $options_ids_refs Reference to [ <processor_id> => <field_id> ]
	 */
	public $options_ids_refs;

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.5
	 */
	public function register_hooks() {

		// do autodiscounts for logged in user
		add_filter( 'caldera_forms_render_get_form', [ $this, 'do_autodiscount' ] );
		add_filter( 'caldera_forms_submit_get_form', [ $this, 'do_autodiscount' ] );

		// do code discounts at submission stage
		add_filter( 'caldera_forms_submit_get_form', [ $this, 'apply_code_discount' ] );

		// track discounts
		add_action( 'cfc_order_post_processor', [ $this, 'track_cividiscounts' ], 10, 3 );
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

		if ( ! $discounts ) return;

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
	 * Build options ids for price fields.
	 *
	 * @since 1.0
	 * @param array $form The form config
	 * @return array|boolean $options_ids_refs References to [ <processor_id> => <event_id> ], or false
	 */
	public function build_options_ids_refs( $price_field_refs, $form = false ) {

		if ( ! empty( $this->options_ids_refs ) ) return $this->options_ids_refs;

		$discounted_options = $this->get_cividiscounts_by_entity( 'pricesets' );

		$discounted_options = empty( $discounted_options ) ? [] : $discounted_options;

		$this->options_ids_refs = array_reduce( $price_field_refs, function( $refs, $field_id ) use ( $price_field_refs, $discounted_options, $form ) {

			$processor_id = array_search( $field_id, $price_field_refs );

			$processor_id = $this->plugin->helper->parse_processor_id( $processor_id );

			$price_field_field = Caldera_Forms_Field_Util::get_field( $field_id, $form, $apply_filters = true );

			if ( empty( $price_field_field ) || empty( $price_field_field['ID'] ) ) return $refs;

			if ( empty( $discounted_options[$processor_id] ) ) {

				$refs[$field_id] = [
					'field_id' => $field_id,
					'processor_id' => $processor_id,
					'field_options' => array_keys( $price_field_field['config']['option'] )
				];

			} else {

				foreach ( $discounted_options as $discount_id => $discount ) {
					if ( ! empty( array_intersect( $discount['pricesets'], array_keys( $price_field_field['config']['option'] ) ) ) )
						$refs[$field_id] = [
							'field_id' => $field_id,
							'processor_id' => $processor_id,
							'field_options' => array_intersect( $discount['pricesets'], array_keys( $price_field_field['config']['option'] ) )
						];
				}

			}

			return $refs;

		}, [] );

		return $this->options_ids_refs;

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
					if ( ! empty( $result['id'] ) ) {
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
			$discount = false;
		}

		return $discount;
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

		if ( empty( $discount['pricesets'] ) ) {
			// if no discount priceset apply to all options
			$option = $this->apply_discount_to_field_option( $option, $field, $price_field_value, $discount );
		} elseif ( in_array( $price_field_value['id'], $discount['pricesets'] ) ) {
			// otherwise make sure the option is included in the discount priceset
			$option = $this->apply_discount_to_field_option( $option, $field, $price_field_value, $discount );
		}

		return $option;
	}

	/**
	 * Apply discount to a fields option.
	 *
	 * @since 1.0.5
	 * @param array $option The option config
	 * @param array $field The field config
	 * @param array $price_field_value The price field value
	 * @param array $event_discount The discount config
	 * @return array $option The filtered option config
	 */
	public function apply_discount_to_field_option( $option, $field, $price_field_value, $discount ) {

		$label = sprintf( __( '%1$s (Includes automatic discount of: ', 'cf-civicrm' ), $price_field_value['label'] );

		// percentage discount
		if ( $discount['amount_type'] == 1 ) {
			$discounted_amount = $price_field_value['amount'] - $this->plugin->helper->calculate_percentage( $price_field_value['amount'], $discount['amount'] );
			$label .= $discount['amount'] . __( '%)', 'cf-civicrm' );
		}
		// fixed discount
		if ( $discount['amount_type'] == 2 ) {
			$discounted_amount = $price_field_value['amount'] - $discount['amount'];
			$label .= $this->plugin->helper->format_money( $discount['amount'] ) . __( ')', 'cf-civicrm' );
		}
		// filtered option
		$option = [
			'value' => $price_field_value['id'],
			'label' => sprintf( '%1$s - %2$s', $label, $this->plugin->helper->format_money( $discounted_amount ) ),
			'calc_value' => $discounted_amount,
			'disabled' => empty( $option['disabled'] ) ? '' : $option['disabled']
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
		if ( isset( $price_field_value['tax_amount'] ) && $this->plugin->helper->get_tax_invoicing() ) {
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

		if ( empty( $discount ) ) return;

		$options = $this->do_code_discount_options( $discount, $form );

		return $options;
	}

	/**
	 * Applies the code discount at submission stage if applicable.
	 *
	 * @since 1.0.5
	 * @param array $form The form config
	 * @return array $form The form config
	 */
	public function apply_code_discount( $form ) {

		if ( empty( $this->options_ids_refs ) ) return $form;

		$discount_field = $this->plugin->cividiscount->get_discount_fields( $form );
		if ( empty( $discount_field ) ) return $form;

		$discount_field_id = key( $discount_field );

		$code = Caldera_Forms::get_field_data( $discount_field_id, $form );
		if ( empty( $code ) ) return $form;

		$discount = $this->get_by_code( $code );
		if ( empty( $discount ) ) return $form;

		$discounted_entities = $this->plugin->helper->get_entity_ids_from_line_items( $form );
		$entities_discounts = $this->get_entities_discounts( $discounted_entities, $form, false, $discount );

		array_map(
			function( $ref ) use ( $form, $discount, $entities_discounts ) {

				$line_item = $form['processors'][$ref['processor_id']];

				$this->apply_option_filter_discount( $line_item, $ref, $entities_discounts, $form );

			},
			$this->options_ids_refs
		);

		return $form;
	}

	/**
	 * Do autodiscounts for logged in user.
	 *
	 * @since 1.0.5
	 * @param array $form The form config
	 * @return array $form The form config
	 */
	public function do_autodiscount( $form ) {

		// only for logged in/checksum users
		$contact = $this->plugin->helper->current_contact_data_get();
		if ( ! $contact ) return $form;

		$price_field_refs = $this->plugin->helper->build_price_field_refs( $form );
		if ( empty( $price_field_refs ) ) return $form;

		$price_field_option_refs = $this->build_options_ids_refs( $price_field_refs, $form );
		if ( empty( $price_field_option_refs ) ) return $form;

		$discounted_entities = $this->plugin->helper->get_entity_ids_from_line_items( $form );
		if ( empty( $discounted_entities ) ) return $form;

		$autodiscounts = $this->get_entities_discounts( $discounted_entities, $form, true );
		if ( empty( $autodiscounts ) ) return $form;

		array_map(
			function( $field_id, $ref ) use ( $form, $autodiscounts ) {

				$processor_id = $ref['processor_id'];

				if ( empty( $processor_id ) ) return;

				$processor = $form['processors'][$processor_id];

				$this->apply_option_filter_discount( $processor, $ref, $autodiscounts, $form, true );

			},
			array_keys( $price_field_option_refs ),
			$price_field_option_refs
		);

		return $form;
	}

	/**
	 * Applies the 'cfc_filter_price_field_config' filter
	 * for discounts if applicable.
	 *
	 * @since 1.0.5
	 * @param array $line_item The line item processor config
	 * @param array $ref The price field reference containing processor, field, and options data
	 * @param array $discounts The discounts
	 * @param array $form The form config
	 * @param bool $autodiscount Wheather to apply for autodiscounts
	 */
	public function apply_option_filter_discount( $line_item, $ref, $discounts, $form, $autodiscount = false ) {

		if ( $autodiscount ) {
			$contact = $this->plugin->helper->current_contact_data_get();
			if ( ! $contact ) return;
		}

		if ( $line_item['config']['entity_table'] != 'civicrm_contribution' ) {
			$processor = $this->plugin->helper->get_processor_from_magic(
				$line_item['config']['entity_params'],
				$form,
				true
			);
		} else {
			// get order processor, contributions line items
			// don't have an entity_params (associated processor)
			$processor = current(
				$this->plugin->helper->get_processor_by_type( 'civicrm_order', $form )
			);
		}

		$discount = count( $discounts ) > 1 && isset( $discounts[$processor['ID']] )
			? $discounts[$processor['ID']]
			: current( $discounts );

		$price_field_filter_function = function( $field, $form, $price_field ) use ( &$ref, &$discount ) {

			if ( $field['ID'] != $ref['field_id'] ) return $field;

			$field['config']['option'] = array_reduce(
				$price_field['price_field_values'],
				function( $options, $price_field_value ) use ( $field, $discount ) {

					$option = $field['config']['option'][$price_field_value['id']];

					// do discounted option
					$options[$price_field_value['id']] = $this->do_discounted_option( $option, $field, $price_field_value, $discount );

					return $options;

			}, [] );

			return $field;

		};

		if (
			$autodiscount
			&& ! empty( $discount['autodiscount'] )
			&& $this->check_autodiscount(
				$discount['autodiscount'],
				$contact['contact_id'],
				$processor['ID']
			)
		) {

			add_filter(
				'cfc_filter_price_field_config',
				$price_field_filter_function,
				50,
				3
			);

			$this->discounts_used[$processor['ID']] = $discount;

		} elseif ( ! $autodiscount ) {

			add_filter(
				'cfc_filter_price_field_config',
				$price_field_filter_function,
				50,
				3
			);

			$this->discounts_used[$processor['ID']] = $discount;

		}

	}

	/**
	 * Do code discounts for all entities
	 * including events, memberships, and contribution.
	 *
	 * @since 1.0
	 * @since 1.0.5 Added support for memberships and contributions
	 * @param array $discount The discount config
	 * @param array $form The forms config
	 * @return array $options The field options
	 */
	public function do_code_discount_options( $discount, $form ) {

		if ( empty( $discount ) ) return;

		// price field references
		$price_field_refs = $this->plugin->helper->build_price_field_refs( $form );
		// options fields refs
		$price_field_option_refs = $this->build_options_ids_refs( $price_field_refs, $form );

		// field configs
		$fields = array_reduce(
			$price_field_refs,
			function( $fields, $field_id ) use ( $form ) {
				$fields[$field_id] = Caldera_Forms_Field_Util::get_field( $field_id, $form, $apply_filters = true );
				return $fields;
			},
			[]
		);

		$options = array_reduce(
			$price_field_option_refs,
			function( $discounted_options, $ref ) use ( $fields, &$form, $discount ) {

				$processor_id = $ref['processor_id'];

				if ( empty( $processor_id ) ) return $discounted_options;

				$processor = $form['processors'][$processor_id];

				// no need to check entities if options are set in discounted priceset options
				if ( empty( array_intersect( $ref['field_options'], $discount['pricesets'] ) ) ) {

					// discount entity for this line item
					$discount_entity = $this->get_discount_entity_map(
						$processor['config']['entity_table']
					);

					// check applied discount is for an event, membership or contribution present on the form
					switch ( $processor['config']['entity_table'] ) {
						// events based discount
						case 'civicrm_participant':
							$participant = $this->plugin->helper->get_processor_from_magic(
								$processor['config']['entity_params'],
								$form,
								true
							);
							if (
								! in_array(
									$participant['config']['id'],
									$discount[$discount_entity]
								)
							) return $discounted_options;
							break;

						// membership based discount
						case 'civicrm_membership':
							$membership = $this->plugin->helper->get_processor_from_magic(
								$processor['config']['entity_params'],
								$form,
								true
							);
							if (
								! in_array(
									$membership['config']['membership_type_id'],
									$discount[$discount_entity]
								)
							) return $discounted_options;
							break;

						// contribution based discount
						case 'civicrm_contribution':
							$order = current(
								$this->plugin->helper->get_processor_by_type( 'civicrm_order', $form )
							);

							// bail if no order or no contribution page
							if ( empty( $order ) || empty( $order['config']['contribution_page_id'] ) ) return $discounted_options;

							if (
								! in_array(
									$order['config']['contribution_page_id'],
									$discount[$discount_entity]
								)
							) return $discounted_options;
							break;
					}

				}

				$field = $fields[$ref['field_id']];

				$price_field = $this->plugin->fields->presets_objects['civicrm_price_sets']->get_price_field_from_config( $field );

				$field['config']['option'] = array_reduce( $price_field['price_field_values'], function( $options, $price_field_value ) use ( $field, $discount ) {

					$option = $field['config']['option'][$price_field_value['id']];

					// do discounted option
					$options[$price_field_value['id']] = $this->do_discounted_option( $option, $field, $price_field_value, $discount );

					return $options;

				}, [] );

				$options = array_reduce( $field['config']['option'], function( $options, $option ) use ( $ref, $field, $form ) {
					$field_option_id = $ref['field_id'] . '_' . $form['form_count'] . '_' . $option['value'];
					$options[$field_option_id] = $field['config']['option'][$option['value']];
					$options[$field_option_id]['field_id'] = $ref['field_id'];
					return $options;
				}, [] );

				$form['fields'][$ref['field_id']] = $discounted_options + $options;

				return $discounted_options + $options;
			},
			[]
		);

		return $options;
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

	/**
	 * Get the discount entity for a processor type.
	 *
	 * @since 1.0.5
	 * @param string $processor_type The processor type
	 * @return string $entity The discount entity
	 */
	public function get_discount_entity_map( $processor_type ) {
		$map = [
			'civicrm_contribution' => 'contributions',
			'civicrm_participant' => 'events',
			'civicrm_membership' => 'memberships'
		];

		return $map[$processor_type];
	}

	/**
	 * Retrieves the CiviDiscounts for each processor/entity.
	 *
	 * @since 1.0.5
	 * @param array $entities_ids Array holding the processor id
	 * and its entity (event, membership, or contribution id) [<processor_id> => <entity_id>]
	 * @param array $form The form config
	 * @param bool $autodiscount
	 * @param array|bool $discount A cividiscount
	 * @return array $form
	 */
	public function get_entities_discounts( $entities_ids, $form, $autodiscount = false, $discount = false ) {

		if ( ! empty( $this->entities_cividiscounts ) ) return $this->entities_cividiscounts;

		$this->entities_cividiscounts = array_reduce( array_keys( $entities_ids ), function( $discounts, $processor_id ) use ( $entities_ids, $form, $autodiscount, $discount ) {

			$processor = $form['processors'][$processor_id];

			switch ( $processor['type'] ) {
				case 'civicrm_participant':
					if ( $discount ) {
						if ( in_array( $processor['config']['id'], $discount['events'] ) ) {
							$discounts[$processor['ID']] = $discount;
						}
					} else {
						$cividiscounts = $this->get_cividiscounts_by_entity( 'events', $autodiscount );
						array_map( function( $discount ) use ( &$discounts, $processor ) {
							if ( in_array( $processor['config']['id'], $discount['events'] ) ) {
								$discounts[$processor['ID']] = $discount;
							}
						}, $cividiscounts );
					}
					break;

				case 'civicrm_membership':
					if ( $discount ) {
						if ( in_array( $processor['config']['membership_type_id'], $discount['memberships'] ) ) {
							$discounts[$processor['ID']] = $discount;
						}
					} else {
						$cividiscounts = $this->get_cividiscounts_by_entity( 'memberships', $autodiscount );
						array_map( function( $discount ) use ( &$discounts, $processor ) {
							if ( in_array( $processor['config']['membership_type_id'], $discount['memberships'] ) ) {
								$discounts[$processor['ID']] = $discount;
							}
						}, $cividiscounts );
					}
					break;

				case 'civicrm_order':
					if ( $discount ) {
						if ( in_array( $processor['config']['contribution_page_id'], $discount['contributions'] ) ) {
							$discounts[$processor['ID']] = $discount;
						}
					} else {
						$cividiscounts = $this->get_cividiscounts_by_entity( 'contributions', $autodiscount );
						array_map( function( $discount ) use ( &$discounts, $processor ) {
							if ( in_array( $processor['config']['contribution_page_id'], $discount['contributions'] ) ) {
								$discounts[$processor['ID']] = $discount;
							}
						}, $cividiscounts );
					}
					break;
			}

			return $discounts;

		}, [] );

		return $this->entities_cividiscounts;

	}

	/**
	 * Track CiviDiscounts.
	 *
	 * @since 1.0.5
	 * @param array $order The order with it's line items
	 * @param array $config The order processor config
	 * @param array $form The form config
	 */
	public function track_cividiscounts( $order, $config, $form ) {

		if ( empty( $order ) || empty( $order['id'] ) || empty( $order['line_items'] ) ) return;

		if ( empty( $this->discounts_used ) ) return;

		if (
			empty( $this->plugin->helper->price_field_refs )
			|| empty( $this->options_ids_refs )
		) return;

		$price_field_refs = $this->plugin->helper->price_field_refs;
		$price_field_option_refs = $this->options_ids_refs;

		$discounted_entities = $this->plugin->helper->get_entity_ids_from_line_items( $form );
		$entities_discounts = $this->get_entities_discounts( $discounted_entities, $form );

		$processors = array_reduce(
			array_keys($entities_discounts),
			function( $processors, $processor_id ) use ( $form, $discounted_entities, $entities_discounts ) {
				$processors[$processor_id] = [
					'type' => $form['processors'][$processor_id]['type'],
					'entity_id' => $discounted_entities[$processor_id],
					'processor_id' => $processor_id,
				];
				return $processors;
			},
			[]
		);

		array_map( function( $line_item ) use ( $processors, $order, $entities_discounts ) {

			$entity = str_replace( 'civicrm_', '', $line_item['entity_table'] );
			$params = ['id' => $line_item['entity_id']];

			if ( $line_item['entity_table'] == 'civicrm_contribution' ) {
				$params['return'] = ['contribution_page_id'];
			}

			try {
				$result = civicrm_api3( $entity, 'getsingle', $params );
			} catch ( CiviCRM_API3_Exception $e ) {
				$result = false;
			}

			if ( ! $result ) return;

			array_map( function( $processor ) use ( $line_item, $result, $order, $entities_discounts ) {

				if ( $processor['type'] == 'civicrm_membership' ) {

					if ( empty( $result['membership_type_id'] ) ) return;
					if ( $processor['entity_id'] != $result['membership_type_id'] ) return;
					if ( $line_item['entity_id'] != $result['id'] ) return;

					if ( empty( $entities_discounts[$processor['processor_id']] ) ) return;
					$discount = $entities_discounts[$processor['processor_id']];
					// safe to track discount
					try {
						$discount_track = civicrm_api3( 'DiscountTrack', 'create', [
							'item_id' => $discount['id'],
							'contact_id' => $order['contact_id'],
							'contribution_id' => $order['id'],
							'entity_table' => $line_item['entity_table'],
							'entity_id' => $processor['entity_id'],
							'description' => $result['membership_name']
						] );
					} catch ( CiviCRM_API3_Exception $e ) {
						Civi::log()->debug( "Unable to track discount {$discount['code']} for contribution id {$order['id']} and item id {$line_item['id']}" );
					}

				} elseif ( $processor['type'] == 'civicrm_participant' ) {

					if ( empty( $result['event_id'] ) ) return;
					if ( $processor['entity_id'] != $result['event_id'] ) return;
					if ( $line_item['entity_id'] != $result['id'] ) return;

					if ( empty( $entities_discounts[$processor['processor_id']] ) ) return;
					$discount = $entities_discounts[$processor['processor_id']];
					// safe to track discount
					try {
						$discount_track = civicrm_api3( 'DiscountTrack', 'create', [
							'item_id' => $discount['id'],
							'contact_id' => $order['contact_id'],
							'contribution_id' => $order['id'],
							'entity_table' => $line_item['entity_table'],
							'entity_id' => $processor['entity_id'],
							'description' => $result['event_title']
						] );
					} catch ( CiviCRM_API3_Exception $e ) {
						Civi::log()->debug( "Unable to track discount {$discount['code']} for contribution id {$order['id']} and item id {$line_item['id']}" );
					}

				} elseif ( $processor['type'] == 'civicrm_order' ) {

					if ( empty( $result['contribution_page_id'] ) ) return;
					if ( $processor['entity_id'] != $result['contribution_page_id'] ) return;
					if ( $line_item['entity_id'] != $result['id'] ) return;

					if ( empty( $entities_discounts[$processor['processor_id']] ) ) return;
					$discount = $entities_discounts[$processor['processor_id']];

					// safe to track discount
					try {
						$contribution_page = civicrm_api3( 'ContributionPage', 'getsingle', [
							'id' => $result['contribution_page_id'],
							'return' => ['title']
						] );

						$discount_track = civicrm_api3( 'DiscountTrack', 'create', [
							'item_id' => $discount['id'],
							'contact_id' => $order['contact_id'],
							'contribution_id' => $order['id'],
							'entity_table' => 'civicrm_contribution_page',
							'entity_id' => $processor['entity_id'],
							'description' => $contribution_page['title']
						] );
					} catch ( CiviCRM_API3_Exception $e ) {
						Civi::log()->debug( "Unable to track discount {$discount['code']} for contribution id {$order['id']} and item id {$line_item['id']}" );
					}
				}

			}, $processors );

		}, $order['line_items'] );

	}

}
