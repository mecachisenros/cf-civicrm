<?php
$counties = caldera_forms_civicrm()->helper->get_counties();
if ( isset( $field['config']['civicrm_state_province'] ) ) {
	$state_province_field = Caldera_Forms::get_field_by_slug( str_replace( '%', '', $field['config']['civicrm_state_province'] ), $form );
}
?>
<?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<select data-placeholder="<?php echo esc_attr( $field['config']['placeholder'] ); ?>" id="<?php echo esc_attr( $field_id . '_cf_civicrm_county' ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="cfc-select2 <?php echo esc_attr( $field_class ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?>>
			<?php

			if ( empty( $field['config']['placeholder'] ) ) {
				echo '<option value="">' . ( ! empty( $field['hide_label'] ) ? esc_html( $field['label'] ) : null ) . '</option>';
			} else {
				$sel = '';
				if ( empty( $field_value ) ) $sel = ' selected="selected"';
				echo '<option value="" disabled="disabled" ' . $sel . '>' . esc_html( $field['config']['placeholder'] ) . '</option>';
			}

			foreach( $counties as $key => $value ) {
				$selected = '';
				if ( ! empty( $field_value ) && $field_value == $key ) $selected = ' selected="selected"';
				echo '<option value="' . esc_attr( $key ) . '" data-crm-state-province-id="' . esc_attr( $value['state_province_id'] ) . '"' . $selected . '>' . esc_html( $value['name'] ) . '</option>';
			 } ?>
		</select>
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>

<?php ob_start(); ?>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {

		var stateProvinceFieldId = '<?php isset( $state_province_field ) ? esc_attr_e( $state_province_field['ID'] ) : print( 'false' ); ?>',
		placeholder = '<?php echo esc_attr( $field['config']['placeholder'] ); ?>';

		var init = function( countyField ) {

			var stateProvinceField = stateProvinceFieldId ? $( 'select[data-field="' + stateProvinceFieldId + '"]' ) : $( 'select[id*="_cf_civicrm_state"]' ),
			allCounties = $( 'option', countyField );

			if ( stateProvinceField == 'undefined' ) return;

			stateProvinceField.on( 'change', function() {

				var stateProvinceId = $( this ).val();

				var counties = [];
				if ( ! countyField.data( 'counties' ) ) {
					countyField.data( 'counties', allCounties );
					counties = countyField.data( 'counties' );
				} else {
					counties = countyField.data( 'counties' );
				}

				var options = counties.filter( function( index, option ) {
					return option.dataset.crmStateProvinceId == stateProvinceId;
				} );

				if ( ! options.length ) options = new Option( 'N/A', 0 );

				countyField.html( options );

			} ).trigger( 'change' );

			return countyField;
		}

		$( document ).on( 'cf.form.init cf.add', function( e, data ) {

			var countyField = stateProvinceFieldId ? $( 'select[data-field="<?php echo esc_attr( $field_base_id ) ?>"]' ) : $( 'select[id*="_cf_civicrm_county"]' );

			var defaultValue = '<?php echo esc_attr( $field['config']['default'] ); ?>',
			fieldId = '<?php echo esc_attr( $field_id ) ?>';

			// init event
			if ( data && data.fieldIds && data.fieldIds.indexOf( fieldId ) != -1 ) {
				init( countyField ).val( defaultValue ).cfcSelect2();
			}
			// add event
			if ( data && data.field && data.field == fieldId ) {
				init( countyField ).val( defaultValue ).cfcSelect2();
			}
		} );

	} );
</script>
<?php
	$script_template = ob_get_clean();
	if( ! empty( $form[ 'grid_object' ] ) && is_object( $form[ 'grid_object' ] ) ){
		$form[ 'grid_object' ]->append( $script_template, $field[ 'grid_location' ] );
	}else{
		echo $script_template;
	}
