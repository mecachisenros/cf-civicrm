<?php

/**
 * CiviCRM Caldera Forms Price Sets Presets Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Price_Sets_Presets {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 */
	public $plugin;

	/**
	 * The custom fields data array.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $processors The custom fields data array
	 */
	public $price_sets;

	/**
	 * Disable all fields flag.
	 *
	 * @since 1.0
	 * @access public
	 * @var boolean $disable_all_fields
	 */
	public $disable_all_fields = false;

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->price_sets = $this->plugin->helper->cached_price_sets();
		// register Caldera Forms callbacks
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 0.4.4
	 */
	public function register_hooks() {

		// adds price field options Presets
		add_filter( 'caldera_forms_field_option_presets', [ $this, 'price_field_options_presets' ] );

		// auto-populate Price Fields
		add_action( 'caldera_forms_autopopulate_types', [ $this, 'autopopulate_price_field_types' ] );
		add_filter( 'caldera_forms_render_get_field', [ $this, 'autopopulate_price_field_values' ], 10, 2 );
		add_filter( 'caldera_forms_render_setup_field', [ $this, 'autopopulate_price_field_values' ], 10, 2 );

		add_filter( 'caldera_forms_render_field_structure', [ $this, 'autopopulate_price_field_values' ], 10, 2 );

	}

	/**
	 * Adds Price Sets options Presets.
	 *
	 * @uses 'caldera_forms_field_option_presets' filter
	 *
	 * @since 0.4.4
	 *
	 * @param array $presets The existing presets
	 * @return array $presets The modified presets
	 */
	public function price_field_options_presets( $presets ) {

		if ( $this->price_sets ) {
			$price_fields = [];
			foreach ( $this->price_sets as $price_set_id => $price_set ) {
				foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
					$options = [];
					foreach ( $price_field['price_field_values'] as $value_id => $price_field_value) {
						$options[] = $value_id.'|'.$price_field_value['label'].' - '.$price_field_value['amount'].'|'.$price_field_value['amount'];
					}
					$price_fields['price_field_'.$price_field_id] = [
						'name' => sprintf( __( 'CiviCRM Price Set: %1$s - Price Field: %2$s', 'caldera-forms-civicrm' ), $price_set['title'], $price_field['label'] ),
						'data' => $options,
					];
				}
			}
			$presets = array_merge( $presets, $price_fields );
		}

		return $presets;
	}


	/**
	 * Autopoulate Price Field options.
	 *
	 * @since 0.4.4
	 */
	public function autopopulate_price_field_types() {

		if ( $this->price_sets ) {
			foreach ( $this->price_sets as $price_set_id => $price_set ) {
				echo '<optgroup label="' . __( 'CiviCRM Price Set - ' . $price_set['title'], 'caldera-forms-civicrm' ) . '">';
				foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
					echo "<option value=\"cfc_price_field_$price_field_id\"{{#is auto_type value=\"cfc_price_field_$price_field_id\"}} selected=\"selected\"{{/is}}>" . __( 'Price Field - ' . $price_field['label'] , 'caldera-forms-civicrm' ) . "</option>";
				}
				echo '</optgroup>';
			}
		}
	}

	/**
	 * Autopopulate Price Field values.
	 *
	 * @since 0.4.4
	 * @param  array $field The field config
	 * @param  array $form The Form config
	 * @return array The filtered field config
	 */
	public function autopopulate_price_field_values( $field, $form ) {

		// filter field structure
		if ( current_filter() == 'caldera_forms_render_field_structure' )
			$field = $this->filter_price_field_structure( $field, $form );

		if ( ! $this->is_price_field_field( $field, $form ) ) return $field;

		/**
		 * if we reach here, current $field is a 'price_field' field
		 */
		$price_field = $this->get_price_field_from_config( $field );

		// remove field if not active
		if ( ! $this->is_price_field_active( $price_field ) )
			return false;

		// populate field options
		$field['config']['option'] = array_reduce( $price_field['price_field_values'], function( $options, $price_field_value ) use ( $field ) {

			$option = [ 
				'value' => $price_field_value['id'],
				'label' => sprintf( '%1$s - %2$s', $price_field_value['label'], $this->plugin->helper->format_money( $price_field_value['amount'] ) ),
				'calc_value' => $price_field_value['amount'],
				'disabled' => $this->disable_all_fields
			];

			if ( $price_field_value['tax_amount'] && $this->plugin->helper->get_tax_settings()['invoicing'] ) {
				$option['calc_value'] += $price_field_value['tax_amount'];
				$option['label'] = $this->plugin->helper->format_tax_label( $price_field_value['label'], $price_field_value['amount'], $price_field_value['tax_amount'] );
			}

			$options[$price_field_value['id']] = $option;
			return $options;

		}, [] );

		/**
		 * Filter autopopulated price fields.
		 *
		 * Triggers for each autopopualted price field at both config and setup stages,
		 * uses both 'caldera_forms_render_get_field' and 'caldera_forms_render_setup_field' filters.
		 *
		 * @since 1.0
		 * @param array $field The field config
		 * @param array $form The form config
		 * @param array $price_field The price field and it's price_field_values
		 * @param string $current_filter The current filter
		 */
		$field = apply_filters( 'cfc_filter_price_field_config', $field, $form, $price_field, $current_filter = current_filter() );

		return $field;
	}

	/**
	 * Filter autopopulated price field's field structure.
	 *
	 * @since 1.0
	 * @param array $field The field structure
	 * @param array $form The form config
	 * @return array $field The filtered field
	 */
	public function filter_price_field_structure( $field, $form ) {

		if ( empty( $field['field']['config']['auto'] ) ) return $field;

		if ( strpos( $field['field']['config']['auto_type'], 'cfc_price_field_' === false ) ) return $field;

		/**
		 * if we reach here, current $field is a 'price_field' field
		 */
		$price_field = $this->get_price_field_from_config( $field['field'] );

		/**
		 * Chance to alter autopopulated price fields.
		 *
		 * @since 1.0
		 * @param array $field The field config
		 * @param array $form The form config
		 * @param array $price_field The price field and it's price_field_values
		 */
		$field = apply_filters( 'cfc_filter_price_field_structure', $field, $form, $price_field );

		return $field;
	}

	/**
	 * Check if price field is active based on active_on/expire_on.
	 *
	 * @since 1.0
	 * @param array $price_field The price field
	 * @return boolean $is_active Whether is active or not
	 */
	public function is_price_field_active( $price_field ) {

		$now = date( 'Y-m-d H:m:i' );
		$active_on = isset( $price_field['active_on'] ) && array_key_exists( 'active_on', $price_field ) ? $price_field['active_on'] : $now;
		$expire_on = isset( $price_field['expire_on'] ) && array_key_exists( 'expire_on', $price_field ) ? $price_field['expire_on'] : $now;

		return ( $now >= $active_on ) && ( $now <= $expire_on );

	}

	/**
	 * Get price field config from field config.
	 *
	 * @since 1.0
	 * @param array $field Field config
	 * @return array $price_field Price field config
	 */
	public function get_price_field_from_config( $field ) {
		$price_field_id = ( int ) str_replace( 'cfc_price_field_', '', $field['config']['auto_type'] );
		return $this->plugin->helper->get_price_set_column_by_id( $price_field_id, 'price_field' );
	}

	/**
	 * Check if field is a price field.
	 *
	 * @since 1.0
	 * @param array $field Field config
	 * @param array $form Form config
	 * @return boolean $is_price_field_field Whether the field is a price field or not
	 */
	public function is_price_field_field( $field, $form ) {

		if ( empty( $field['config']['auto'] ) ) return false;

		if ( strpos( $field['config']['auto_type'], 'cfc_price_field_' ) === false ) return false;

		if ( ! $this->price_sets ) return false;

		return true;
	}

}
