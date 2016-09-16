<?php

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

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field() ?>
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
<div id="contact_type" class="caldera-config-group">
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
<div id="contact_type" class="caldera-config-group">
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
	foreach ( $activityFields as $key => $value ) { ?>
	<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
			<?php if ( $key == 'source_record_id' ) { ?>
				<p><?php _e( 'Default is set to the Contact that submits the form.', 'caldera-forms-civicrm' ); ?></p>
			<? } ?>
		</div>
	</div>
<?php } ?>

<script>
	jQuery(document).ready( function() {
		jQuery('#source_record_id select').prop( 'disabled', true );
	} );
</script>
