<?php

// Get Contact Types
$contactTypeResult = civicrm_api3( 'ContactType', 'get', array(
    'sequential' => 0,
    'is_active' => 1,
    'parent_id' => array('IS NULL' => 1),
));

$contactSubTypeResult = civicrm_api3( 'ContactType', 'get', array(
    'sequential' => 1,
    'parent_id' => array('IS NOT NULL' => 1),
));

?>

<!-- Contact Link -->
<h2>Contact Link</h2>
<div id="contact_link" class="caldera-config-group">
    <label><?php echo __('Link'); ?></label>
    <div class="caldera-config-field">
        <?php CiviCRM_Caldera_Forms::contact_link_field(); ?>     
    </div>
</div>
<hr style="clear: both;" />

<!-- Contact Type -->
<h2>Contact Type</h2>
<div id="contact_type" class="caldera-config-group">
    <label><?php echo __('Contact Type'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[contact_type]">
        <?php foreach( $contactTypeResult['values'] as $key => $value) { ?>
            <option value="<?php echo $value['name']; ?>" {{#is contact_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo $value['label']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>

<!-- Contact Sub-Type -->
<div id="contact_sub_type" class="caldera-config-group">
    <label><?php echo __('Contact Sub-Type'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[contact_sub_type]">
        <option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
        <?php foreach( $contactSubTypeResult['values'] as $key => $value) { ?>
            <option value="<?php echo $value['name']; ?>" {{#is contact_sub_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo $value['label'] . ' [' . $contactTypeResult['values'][$value['parent_id']]['label'] . ']'; ?></option>
        <?php } ?>
        </select>
    </div>
</div>
<hr/>

<!-- Contact Fields -->
<!-- <button id="contact_fields_btn" class="button fields-btn" type="button" style="clear: both;">Show Standard fields</button>
<div id="contact_fields_container" style="clear: both;"> -->
    <h2 style="display: inline-block;">Standard Fields</h2>
    <!-- <button id="clear_fields" style="float: right;" type="button">Clear fields</button> -->
    <?php
        $contactFieldsResult = civicrm_api3( 'Contact', 'getfields', array(
            'sequential' => 1,
        ));

        $contactFields = array();
        foreach ( $contactFieldsResult['values'] as $key => $value ) {
            if( in_array( $value['name'], CiviCRM_Caldera_Forms::$contact_fields ) ){
                $contactFields[$value['name']] = $value['title'];
            }
        }
        unset( $contactFields['id'], $contactFields['contact_type'], $contactFields['contact_sub_type'] );
        $contactFields = array_diff_key( $contactFields, CiviCRM_Caldera_Forms::get_contact_custom_fields() );

        foreach( $contactFields as $key => $value ) { ?>
        <div id="<?php echo $key; ?>" class="caldera-config-group">
            <label><?php echo __($value); ?> </label>
            <div class="caldera-config-field">
              <?php echo __('{{{_field slug="' . $key . '"}}}'); ?>
            </div>
        </div>
    <?php } ?>
    <hr style="clear: both;" />
<!-- </div> -->

<!-- Contact Custom Fields -->
<!-- <button id="custom_fields_btn" class="button fields-btn" type="button" style="clear: both;">Show Custom fields</button>
<div id="custom_fields_container"> -->
    <h2 style="display: inline-block;">Custom Fields</h2>
    <!-- <button id="clear_custom_fields" style="float: right;" type="button">Clear fields</button> -->
    <?php

        $contactCustomFields = CiviCRM_Caldera_Forms::get_contact_custom_fields();

        foreach( $contactCustomFields as $key => $value ) { ?>
        <div id="<?php echo $key; ?>" class="caldera-config-group">
            <label><?php echo __($value); ?> </label>
            <div class="caldera-config-field">
              <?php echo __('{{{_field slug="' . $key . '"}}}'); ?>
            </div>
        </div>
    <?php } ?>
<!-- </div> -->
<?php
/*
if ( class_exists( 'Caldera_Forms_Processor_UI' ) ) {

  //get the field  configuration
  $cf_civicrm_contact_fields = cf_civicrm_contact_fields();

  //get HTML for UI
  $config_fields = Caldera_Forms_Processor_UI::config_fields( $cf_civicrm_contact_fields );

  //echo UI
  echo $config_fields;
}
*/
?>
