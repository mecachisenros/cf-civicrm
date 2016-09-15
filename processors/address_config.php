<?php
    $addressFields = civicrm_api3('Address', 'getfields', array(
		'sequential' => 1,
	));

    $addressLocationType = civicrm_api3('Address', 'getoptions', array(
        'sequential' => 1,
        'field' => "location_type_id",
    ));

    $fields = array( 'is_primary', 'is_billing', 'street_address', 'supplemental_address_1', 'supplemental_address_2', 'city', 'state_province_id', 'postal_code', 'country_id');
?>

<h2>Contact Link</h2>
<div id="contact_link" class="caldera-config-group">
    <label><?php echo __('Link to'); ?></label>
    <div class="caldera-config-field">
        <?php CiviCRM_Caldera_Forms_Helper::contact_link_field(); ?>
        <p>Select witch contact you want to link this processor to.</p>
    </div>
</div>
<hr style="clear: both;" />

<h3>Address Location Type</h3>
<div id="location_type_id" class="caldera-config-group">
    <label><?php echo __('Address Location Type'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[location_type_id]">
        <option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
        <?php foreach( $addressLocationType['values'] as $key => $value) { ?>
            <option value="<?php echo $value['key']; ?>" {{#is location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>
<hr style="clear: both;" />

<h2 style="display: inline-block;">Address Fields</h2>
<?php
    foreach( $addressFields['values'] as $key => $value ) {
        if( in_array($value['name'], $fields ) ){ ?>
    <div id="<?php echo $value['name']; ?>" class="caldera-config-group">
        <label><?php echo __($value['title']); ?> </label>
        <div class="caldera-config-field">
          <?php echo __('{{{_field slug="' . $value['name'] . '"}}}'); ?>
        </div>
    </div>
<?php } } ?>
<hr style="clear: both;" />
