<?php

	$activities = civicrm_api3('Activity', 'getoptions', array(
		'sequential' => 1,
		'field' => "activity_type_id",
	));

	$activity_status = civicrm_api3('Activity', 'getoptions', array(
		'sequential' => 1,
		'field' => 'status_id',
	));

	$campaign_id = civicrm_api3('Campaign', 'get', array(
		'sequential' => 1,
	));

    $activityFieldsResult = civicrm_api3( 'Activity', 'getfields', array(
        'sequential' => 1,
    ));

    $activityFields = array();
    foreach ( $activityFieldsResult['values'] as $key => $value ) {
        if( !in_array( $value['name'], CiviCRM_Caldera_Forms_Helper::$activity_fields ) ){
            $activityFields[$value['name']] = $value['title'];
        }
    }

?>

<h2>Contact Link</h2>
<div id="contact_link" class="caldera-config-group">
    <label><?php echo __('Link'); ?></label>
    <div class="caldera-config-field">
        <?php CiviCRM_Caldera_Forms_Helper::contact_link_field() ?>
        <p>Select to witch contact you want to link this processor to.</p>
    </div>
</div>
<hr style="clear: both;" />

<!-- Activity Type -->
<h2>Activity</h2>
<div id="contact_type" class="caldera-config-group">
    <label><?php echo __('Activity Type'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[activity_type_id]">
        <?php foreach( $activities['values'] as $key => $value) { ?>
            <option value="<?php echo $value['key']; ?>" {{#is activity_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>

<!-- Activity status -->
<div id="contact_type" class="caldera-config-group">
    <label><?php echo __('Activity Status'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[status_id]">
        <?php foreach( $activity_status['values'] as $key => $value) { ?>
            <option value="<?php echo $value['key']; ?>" {{#is status_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>

<!-- Campaign -->
<div id="contact_type" class="caldera-config-group">
    <label><?php echo __('Campaign'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[campaign_id]">
        <option value="" {{#is campaign_id value=""}}selected="selected"{{/is}}></option>
        <?php foreach( $campaign_id['values'] as $key => $value) { ?>
            <option value="<?php echo $value['id']; ?>" {{#is campaign_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo $value['title']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>
<hr style="clear: both;" />

<h2>Activity fields</h2>
<?php
    foreach( $activityFields as $key => $value ) { ?>
    <div id="<?php echo $key; ?>" class="caldera-config-group">
        <label><?php echo __($value); ?> </label>
        <div class="caldera-config-field">
          <?php echo __('{{{_field slug="' . $key . '"}}}'); ?>
          <?php if( $key == 'source_record_id') echo '<p>Default is set to the Contact that submits the form.</p>'; ?>
        </div>
    </div>
<?php } ?>

<script>
    jQuery( document).ready( function(){
        jQuery('#source_record_id select').prop('disabled', true);
    } );
</script>
