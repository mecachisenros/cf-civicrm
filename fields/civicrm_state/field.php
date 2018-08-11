<?php
$state = caldera_forms_civicrm()->helper->get_state_province();
if ( isset( $field['config']['civicrm_country'] ) ) {
	$country_field = Caldera_Forms::get_field_by_slug( str_replace( '%', '', $field['config']['civicrm_country'] ), $form );
}
?>
<?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo esc_attr( $field_id . '_cf_civicrm_state' ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="cfc-select2 <?php echo esc_attr( $field_class ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?>>
			<?php

			if ( empty( $field['config']['placeholder'] ) ) {
				echo '<option value="">' . ( ! empty( $field['hide_label'] ) ? esc_html( $field['label'] ) : null ) . '</option>';
			} else {
				$sel = '';
				if ( empty( $field_value ) ) $sel = ' selected="selected"';
				echo '<option value="" disabled="disabled" ' . $sel . '>' . esc_html( $field['config']['placeholder'] ) . '</option>';
			}

			foreach( $state as $key => $value ) {
				$selected = '';
				if ( ! empty( $field_value ) && $field_value == $key ) $selected = ' selected="selected"';
				echo '<option value="' . esc_attr( $key ) . '" data-crm-country-id="' . esc_attr( $value['country_id'] ) . '"' . $selected . '>' . esc_html( $value['name'] ) . '</option>';
			 } ?>
		</select>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
			
			<?php if( $country_field ): ?>
				
				var countries = $( 'select[data-field="<?php echo esc_attr( $country_field['ID'] ) ?>"]' ),
				states = $( 'select[data-field="<?php echo esc_attr( $field_base_id ) ?>"]' );

				if ( countries !== undefined ) {
					countries.change( function() {
						if ( $( this ).data( 'options' ) == undefined ) {
							$( this ).data( 'options', $( 'select[data-field="<?php echo esc_attr( $field_base_id ) ?>"] option' ).clone() );
						}
						var id = $( this ).val(),
						options = $( this ).data( 'options' ).filter( '[data-crm-country-id="' + id + '"]' );
						states.html( options );
					} ).trigger( 'change' );
				}
			
			<?php else: ?>

				// keep to not break current forms
				var countries = $( 'select[id*="_cf_civicrm_country"]' ),
				states = $( 'select[id*="_cf_civicrm_state"]' );

				if ( countries !== undefined ) {
					countries.change( function(){
						if ( $( this ).data( 'options' ) == undefined ) {
							$( this ).data( 'options', $( 'select[id*="_cf_civicrm_state"] option' ).clone() );
						}
						var id = $( this ).val(),
						options = $( this ).data( 'options' ).filter( '[data-crm-country-id="' + id + '"]' );
						states.html( options );
					}).trigger('change');
				}
			<?php endif; ?>

				// select2 widget
				$( '.cfc-select2' ).cfcSelect2();

			} );
		</script>
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>
