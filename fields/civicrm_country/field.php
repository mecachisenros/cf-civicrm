<?php

try {

    $country = civicrm_api3( 'Country', 'get', array(
        'sequential' => 1,
        'options' => array( 'limit' => 0 ),
        'id' => array( 'IN' => CiviCRM_Caldera_Forms_Helper::get_civicrm_settings( 'countryLimit' ) ),
    ));

} catch ( Exception $e ) {
    // If no countries enabled in CiviCRM localization settings (CiviCRM_Caldera_Forms::get_civicrm_settings('countryLimit')) get all countries instead
    $country = civicrm_api3( 'Country', 'get', array(
        'sequential' => 1,
        'options' => array( 'limit' => 0 ),
    ));

}

echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<?php ob_start(); ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo esc_attr( $field_id . '_cf_civicrm_country' ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="<?php echo esc_attr( $field_class ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?>>
			<?php

			if ( empty( $field['config']['placeholder'] ) ) {
				echo '<option value="">' . ( ! empty( $field['hide_label'] ) ? esc_html( $field['label'] ) : null ) . '</option>';
			} else {
				$sel = '';
				if ( empty( $field_value ) ) {
					$sel = 'selected';
				}
				echo '<option value="" disabled ' . $sel . '>' . esc_html( $field['config']['placeholder'] ) . '</option>';
			}

			foreach( $country['values'] as $key => $value ) {
				echo '<option value="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['name'] ) . '</option>';

			 } ?>
		</select>
		<?php

		$countries = ob_get_clean();
		if ( ! empty( $field_value ) ) {
			$countries = str_replace( 'value="' . $field_value . '"', 'value="' . $field_value . '" selected="selected"', $countries );
		}
		echo $countries;

		?>

		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>
