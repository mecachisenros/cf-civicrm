<?php

$address_fields = civicrm_api3( 'Address', 'getfields', [
	'sequential' => 1,
] );

$address_location_type = civicrm_api3( 'Address', 'getoptions', [
	'sequential' => 1,
	'field' => 'location_type_id',
] );

$fields = caldera_forms_civicrm()->processors->processors['address']->fields;

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_is_override" type="checkbox" name="{{_name}}[is_override]" value="1" {{#if is_override}}checked="checked"{{/if}}><?php _e( 'Write empty/blank fields values.', 'cf-civicrm' ); ?></label>
		<p class="description"><?php _e( 'If a mapped field\'s value is empty/blank it will still be sent to CiviCRM when this setting is enabled.' ) ?></p>
	</div>
</div>

<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<h3><?php _e( 'Address Location Type', 'cf-civicrm' ); ?></h3>
<div id="{{_id}}_location_type_id" class="caldera-config-group">
	<label><?php echo __( 'Address Location Type', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[location_type_id]">
		<option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $address_location_type['values'] as $key => $value) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<hr style="clear: both;" />

<h2 style="display: inline-block;"><?php _e( 'Address Fields', 'cf-civicrm' ); ?></h2>
<?php foreach( $address_fields['values'] as $key => $value ) {
	if ( in_array( $value['name'], $fields ) ) { ?>
	<div id="{{_id}}_<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value['title'] ); ?> </label>
		<div class="caldera-config-field">
		  <?php echo '{{{_field slug="' . $value['name'] . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>

<hr style="clear: both;" />

<h2><?php _e( 'Custom Fields', 'cf-civicrm' ); ?></h2>
<?php foreach ( caldera_forms_civicrm()->helper->get_address_custom_fields() as $key => $custom_field ) { ?>
	<div
		id="{{_id}}_<?php echo esc_attr( $key ); ?>"
		class="caldera-config-group"
		data-entity-column-id="<?php echo esc_attr( $custom_field['extends_entity_column_id'] ); ?>"
		data-entity-column-value="<?php echo esc_attr( json_encode( $custom_field['extends_entity_column_value'] ) ); ?>"
		>
		<label><?php echo esc_html( $custom_field['label'] ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } ?>