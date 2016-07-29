<?php

$country = civicrm_api3('Country', 'get', array(
    'sequential' => 1,
    'options' => array('limit' => 0),
    'iso_code' => array('IN' => CRM_Core_BAO_Country::countryLimit()),
));

echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<?php ob_start(); ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo $field_id . '_cf_civicrm_country'; ?>" data-field="<?php echo $field_base_id; ?>" class="<?php echo $field_class; ?>" name="<?php echo $field_name; ?>" <?php echo $field_required; ?>>

			<?php if( empty( $field['config']['placeholder'] ) ){
					echo '<option value="">' . ( !empty($field['hide_label']) ? $field['label'] : null ) . '</option>';
				}else{
					$sel = '';
					if( empty( $field_value ) ){
						$sel = 'selected';
					}
					echo '<option value="" disabled ' . $sel . '>' . $field['config']['placeholder'] . '</option>';
				}

			foreach ($country['values'] as $key=>$value) {
				echo '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';

			 } ?>
		</select>
		<?php
			$countries = ob_get_clean();
			if( !empty( $field_value ) ){
				$countries = str_replace( 'value="' . $field_value . '"', 'value="' . $field_value . '" selected="selected"', $countries );
			}
			echo $countries;
		?>

		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>
