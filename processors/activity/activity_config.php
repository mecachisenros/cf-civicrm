<?php

$ignore_fields = array( 'target_contact_id', 'source_contact_id', 'assignee_contact_id', 'source_record_id', 'contact_id' );

$activities = civicrm_api3( 'Activity', 'getoptions', array(
	'sequential' => 1,
	'field' => 'activity_type_id',
));

$activity_status = civicrm_api3( 'Activity', 'getoptions', array(
	'sequential' => 1,
	'field' => 'status_id',
));

$campaign_id = civicrm_api3( 'Campaign', 'get', array(
	'sequential' => 1,
	'is_active' => 1,
	'options' => array( 'limit' => 0 ),
));

$activityFieldsResult = civicrm_api3( 'Activity', 'getfields', array(
	'sequential' => 1,
));

$activityFields = array();
foreach ( $activityFieldsResult['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], CiviCRM_Caldera_Forms_Helper::$activity_fields ) ) {
		$activityFields[$value['name']] = $value['title'];
	}
}

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_target_contact_link" type="checkbox" name="{{_name}}[target_contact_link]" value="1" {{#if target_contact_link}}checked="checked"{{/if}}><?php _e( 'Set the Target (With contact) to the contact linked to this activity.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_source_contact_link" type="checkbox" name="{{_name}}[source_contact_link]" value="1" {{#if source_contact_link}}checked="checked"{{/if}}><?php _e( 'Set the Source (Added by) to the contact linked to this activity.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_assignee_contact_link" type="checkbox" name="{{_name}}[assignee_contact_link]" value="1" {{#if assignee_contact_link}}checked="checked"{{/if}}><?php _e( 'Set the Assignee (Assigned to) to the contact linked to this activity.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<p><?php _e( 'NOTE: this settings will override the below fields (Target Contact ID, Source Contact ID, and Assignee Contact ID)', 'caldera-forms-civicrm' ); ?></p>

<hr style="clear: both;" />

</div>
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<!-- Activity Type -->
<h2><?php _e( 'Activity', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_type" class="caldera-config-group">
	<label><?php _e( 'Activity Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[activity_type_id]">
		<?php foreach ( $activities['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is activity_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Activity status -->
<div id="{{_id}}_activity_status" class="caldera-config-group">
	<label><?php _e( 'Activity Status', 'caldera-forms-civicrm' ); ?></label>
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
	<label><?php _e( 'Campaign', 'caldera-forms-civicrm' ); ?></label>
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

<h2><?php _e( 'Activity fields', 'caldera-forms-civicrm' ); ?></h2>
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
	<label><?php _e( 'Target Contact ID', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_target_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[target_contact_id]">
		</select>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e( 'Source Contact ID', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_source_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[source_contact_id]">
		</select>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e( 'Assignee Contact ID', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_assignee_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[assignee_contact_id]">
		</select>
	</div>
</div>

<script>
	jQuery(document).ready( function() {
		var pid_prefix = '#{{_id}}_',
		cfc_select2 = [ {
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
