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
			$presets = array_merge( $price_fields, $presets );
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

		if ( $this->price_sets ) {
			if ( ! empty( $field['config']['auto'] ) ) {
				foreach ( $this->price_sets as $price_set_id => $price_set ) {
					foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
						if( $field['config']['auto_type'] == 'cfc_price_field_' . $price_field_id ) {
							foreach ( $price_field['price_field_values'] as $value_id => $price_field_value ) {
								$field['config']['option'][$value_id] = [
									'value' => $value_id,
									'label' => $price_field_value['label'] . ' - ' . $field['config']['price_field_currency'] . ' ' . $price_field_value['amount'],
									'calc_value' => $price_field_value['amount']
								];
							}
						}
					}
				}
			}
		}

		return $field;
	}

}

