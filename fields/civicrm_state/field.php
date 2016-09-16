<?php

$state = CiviCRM_Caldera_Forms_Helper::get_state_province();

echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<?php ob_start(); ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo esc_attr( $field_id . '_cf_civicrm_state' ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="<?php echo esc_attr( $field_class ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?>>
			<?php

			if ( empty( $field['config']['placeholder'] ) ) {
				echo '<option value="">' . ( ! empty( $field['hide_label'] ) ? esc_html( $field['label'] ) : null ) . '</option>';
			} else {
				$sel = '';
				if( empty( $field_value ) ) {
					$sel = 'selected';
				}
				echo '<option value="" disabled ' . $sel . '>' . esc_html( $field['config']['placeholder'] ) . '</option>';
			}

			foreach( $state as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '" data-crm-country-id="' . esc_attr( $value['country_id'] ) . '">' . esc_html( $value['name'] ) . '</option>';

			 } ?>
		</select>
		<?php

		$states = ob_get_clean();
		if( ! empty( $field_value ) ) {
			$states = str_replace( 'value="' . $field_value . '"', 'value="' . $field_value . '" selected="selected"', $states );
		}
		echo $states;

		?>

		<script type="text/javascript">
			jQuery(document).ready( function() {
				var cfCountries = jQuery('select[id*="_cf_civicrm_country"]');
				var cfStates = jQuery('select[id*="_cf_civicrm_state"]');

				if ( cfCountries !== undefined ) {
					cfCountries.change( function(){
						if ( jQuery(this).data( 'options' ) == undefined ){
							jQuery(this).data( 'options', jQuery('select[id*="_cf_civicrm_state"] option').clone() );
						}
						var id = jQuery(this).val();
						var options = jQuery(this).data( 'options' ).filter( '[data-crm-country-id="' + id + '"]' );
						cfStates.html( options );
					});
				}
			});
		</script>

		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>
