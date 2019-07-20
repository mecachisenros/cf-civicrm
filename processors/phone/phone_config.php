<?php

$phone_fields = civicrm_api3( 'Phone', 'getfields', [
	'sequential' => 1,
] );

$phone_location_type = civicrm_api3( 'Phone', 'getoptions', [
	'sequential' => 1,
	'field' => 'location_type_id',
] );

$phone_type = civicrm_api3( 'Phone', 'getoptions', [
	'field' => 'phone_type_id',
] );

$fields = [ 'is_primary', 'is_billing', 'phone', 'phone_numeric' ];

?>

<!-- Contact Link -->
<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />
<!-- Phone Location type -->
<div id="{{_id}}_location_type_id" class="caldera-config-group">
	<label><?php _e( 'Phone Location Type', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[location_type_id]">
			<option value=""></option>
		<?php foreach( $phone_location_type['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Phone type -->
<div id="{{_id}}_phone_type_id" class="caldera-config-group">
	<label><?php _e( 'Phone Type', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[phone_type_id]">
			<option value=""></option>
		<?php foreach( $phone_type['values'] as $id => $type ) { ?>
			<option value="<?php echo esc_attr( $id ); ?>" {{#is phone_type_id value=<?php echo $id; ?>}}selected="selected"{{/is}}><?php echo esc_html( $type ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<hr style="clear: both;" />

<!-- Phone fields -->
<h2 style="display: inline-block;"><?php _e( 'Phone Fields', 'cf-civicrm' ); ?></h2>
<?php foreach( $phone_fields['values'] as $key => $value ) {
	if ( in_array( $value['name'], $fields ) ) { ?>
	<div id="{{_id}}_<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value['title'] ); ?></label>
		<div class="caldera-config-field">
		  <?php echo '{{{_field slug="' . $value['name'] . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>

<hr style="clear: both;" />
