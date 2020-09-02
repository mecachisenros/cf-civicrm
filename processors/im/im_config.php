<?php
	$imFields = civicrm_api3( 'Im', 'getfields', [
		'sequential' => 1,
	] );

	$imType = civicrm_api3( 'Im', 'getoptions', [
		'sequential' => 1,
		'field' => 'location_type_id',
	] );

	$fields = [ 'name', 'provider_id', 'is_primary', 'is_billing' ];
?>

<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<h2><?php _e( 'Location Type', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_website_type_id" class="caldera-config-group">
	<label><?php echo __('Location Type'); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[location_type_id]">
		<?php foreach( $imType['values'] as $key => $value) { ?>
			<option value="<?php echo $value['key']; ?>" {{#is location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<hr style="clear: both;" />

<h2><?php _e( 'Im Fields', 'cf-civicrm' ); ?></h2>
<?php
	foreach( $imFields['values'] as $key => $value ) {
		if( in_array($value['name'], $fields ) ){ ?>
	<div id="{{_id}}_<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
		<label><?php echo __($value['title']); ?> </label>
		<div class="caldera-config-field">
			<?php echo __('{{{_field slug="' . $value['name'] . '"}}}'); ?>
		</div>
	</div>
<?php } } ?>
<hr style="clear: both;" />
