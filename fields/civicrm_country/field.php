<?php $countries = caldera_forms_civicrm()->helper->get_countries(); ?>
<?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo esc_attr( $field_id . '_cf_civicrm_country' ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="cfc-select2 <?php echo esc_attr( $field_class ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?>>
			<?php

			if ( empty( $field['config']['placeholder'] ) ) {
				echo '<option value="">' . ( ! empty( $field['hide_label'] ) ? esc_html( $field['label'] ) : null ) . '</option>';
			} else {
				$sel = '';
				if ( empty( $field_value ) ) $sel = ' selected="selected"';
				echo '<option value="" disabled="disabled"' . $sel . '>' . esc_html( $field['config']['placeholder'] ) . '</option>';
			}

			foreach( $countries['values'] as $key => $value ) {
				$selected = '';
				if ( ! empty( $field_value ) && $field_value == $value['id'] ) $selected = ' selected="selected"';
				echo '<option value="' . esc_attr( $value['id'] ) . '"' . $selected . '>' . esc_html( $value['name'] ) . '</option>';
			 }

			 ?>
		</select>
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>

<?php ob_start(); ?>
<script>
	jQuery( document ).ready( function( $ ) {
		$( document ).on( 'change cf.form.init cf.add', function( e, field ) {
			$( '.cfc-select2' ).cfcSelect2();
		} )
	} );
</script>
<?php
	$script_template = ob_get_clean();
	if( ! empty( $form[ 'grid_object' ] ) && is_object( $form[ 'grid_object' ] ) ){
		$form[ 'grid_object' ]->append( $script_template, $field[ 'grid_location' ] );
	}else{
		echo $script_template;
	}