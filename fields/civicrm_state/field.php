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
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>

<?php ob_start(); ?>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
	<?php if( $country_field ): ?>

		var init = function() {
			var countries = $( '[data-field="<?php echo esc_attr( $country_field['ID'] ) ?>"]' ),
			states = $( 'select[data-field="<?php echo esc_attr( $field_base_id ) ?>"]' ),
			state_options = states.find( 'option' );

			if ( countries !== undefined ) {
				countries.on( 'change cf.form.init cf.add', function() {
					var id = $( this ).val();
					options = state_options.filter( '[data-crm-country-id="' + id + '"]' );
					states.html( options );
				} );
			}
		}

	<?php else: ?>

		var init = function() {
			// keep to not break current forms
			var countries = $( 'select[id*="_cf_civicrm_country"]' ),
			states = $( 'select[id*="_cf_civicrm_state"]' ),
			state_options = states.find( 'option' );

			if ( countries !== undefined ) {
				countries.on( 'change', function() {
					var id = $( this ).val();
					options = state_options.filter( '[data-crm-country-id="' + id + '"]' );
					states.html( options );
				} );
			}
		}

	<?php endif; ?>
		// select2 widget
		$( document ).on( 'cf.form.init cf.add', function( e, field ) {
			init();
			$( 'select[data-field="<?php echo esc_attr( $field_base_id ) ?>"]' ).cfcSelect2();
		} )

	} );
</script>
<?php
	$script_template = ob_get_clean();
	if( ! empty( $form[ 'grid_object' ] ) && is_object( $form[ 'grid_object' ] ) ){
		$form[ 'grid_object' ]->after( $script_template, $field[ 'grid_location' ] );
	}else{
		echo $script_template;
	}