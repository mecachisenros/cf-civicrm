<?php
	$emailFields = civicrm_api3( 'Email', 'getfields', [
		'sequential' => 1,
	] );

	$emailLocationType = civicrm_api3( 'Email', 'getoptions', [
		'sequential' => 1,
		'field' => 'location_type_id',
	] );

	$fields = [ 'is_primary', 'is_billing', 'email', 'on_hold', 'is_bulkmail' ];
?>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<h2>Email Location Type</h2>
<div id="{{_id}}_location_type_id" class="caldera-config-group">
	<label><?php echo __('Address Location Type'); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[location_type_id]">
		<?php foreach( $emailLocationType['values'] as $key => $value) { ?>
			<option value="<?php echo $value['key']; ?>" {{#is location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<hr style="clear: both;" />

<h2 style="display: inline-block;">Email Fields</h2>
<?php
	foreach( $emailFields['values'] as $key => $value ) {
		if( in_array($value['name'], $fields ) ){ ?>
	<div id="{{_id}}_<?php echo $value['name']; ?>" class="caldera-config-group">
		<label><?php echo __($value['title']); ?> </label>
		<div class="caldera-config-field">
			<?php echo __('{{{_field slug="' . $value['name'] . '"}}}'); ?>
		</div>
	</div>
<?php } } ?>
<hr style="clear: both;" />
