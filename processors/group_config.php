<?php
  $groupsResult = civicrm_api3('Group', 'get', array(
		'sequential' => 1,
		'cache_date' => array('IS NULL' => 1),
		'is_active' => 1,
		'options' => array('limit' => 0),
	));
?>

<h2>Contact Link</h2>
<div id="contact_link" class="caldera-config-group">
    <label><?php echo __('Link to'); ?></label>
    <div class="caldera-config-field">
        <?php CiviCRM_Caldera_Forms::contact_link_field(); ?>
        <p>Select to witch contact you want to link this processor to.</p>   
    </div>
</div>
<hr style="clear: both;" />

<div class="caldera-config-group">
    <label><?php echo __('Group'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[contact_group]">
        <?php foreach( $groupsResult['values'] as $key => $value) { ?>
            <option value="<?php echo $value['id']; ?>" {{#is contact_group value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo $value['title']; ?></option>
            
        <?php } ?>
        </select>
    </div>
</div>
