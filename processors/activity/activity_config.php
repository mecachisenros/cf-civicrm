<?php

$ignore_fields = [ 'target_contact_id', 'source_contact_id', 'assignee_contact_id', 'source_record_id', 'contact_id' ];

$activities = civicrm_api3( 'Activity', 'getoptions', [
	'sequential' => 1,
	'field' => 'activity_type_id',
] );

$activity_status = civicrm_api3( 'Activity', 'getoptions', [
	'sequential' => 1,
	'field' => 'status_id',
] );

$campaign_id = civicrm_api3( 'Campaign', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

$activityFieldsResult = civicrm_api3( 'Activity', 'getfields', [
	'sequential' => 1,
] );

$activityFields = [];
foreach ( $activityFieldsResult['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], caldera_forms_civicrm()->helper->activity_fields ) ) {
		$activityFields[$value['name']] = $value['title'];
	}
}

?>

<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<!-- Activity Type -->
<h2><?php _e( 'Activity', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_activity_type_id" class="caldera-config-group">
	<label><?php _e( 'Activity Type', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[activity_type_id]">
		<?php foreach ( $activities['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is activity_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Activity status -->
<div id="{{_id}}_activity_status_id" class="caldera-config-group">
	<label><?php _e( 'Activity Status', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[status_id]">
		<?php foreach ( $activity_status['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is status_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Campaign -->
<div id="{{_id}}_campaign_id" class="caldera-config-group">
	<label><?php _e( 'Campaign', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[campaign_id]">
		<option value="" {{#is campaign_id value=""}}selected="selected"{{/is}}></option>
		<?php foreach ( $campaign_id['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is campaign_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['title'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<hr style="clear: both;" />

<h2><?php _e( 'Activity fields', 'cf-civicrm' ); ?></h2>
<?php
	foreach ( $activityFields as $key => $value ) {
		if( ! in_array( $key, $ignore_fields ) ) { ?>
	<div id="{{_id}}_<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
			<?php
				echo '{{{_field ';
				if ( $key == 'file_id' ) echo 'type="advanced_file,file" ';
				echo 'slug="' . $key . '"}}}';
			?>
		</div>
	</div>
<?php } } ?>

<div class="caldera-config-group">
	<label><?php _e( 'Target Contact ID', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_target_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[target_contact_id]">
		</select>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e( 'Source Contact ID', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_source_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[source_contact_id]">
		</select>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e( 'Assignee Contact ID', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_assignee_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[assignee_contact_id]">
		</select>
	</div>
</div>

<script>
	jQuery(document).ready( function() {
		var pid_prefix = '#{{_id}}_',
		select2_fields = [ {
				field: 'target_contact_id',
				value: '{{target_contact_id}}'
			},
			{
				field: 'source_contact_id',
				value: '{{source_contact_id}}'
			},
			{
				field: 'assignee_contact_id',
				value: '{{assignee_contact_id}}'
			}
		]
		.map( function( obj ){
			return {
				selector: pid_prefix + obj.field,
				value: obj.value
			}
		} )
		.map( function( field ){
			cfc_select2_defaults( field.selector, field.value );
		} )
	} );
</script>
