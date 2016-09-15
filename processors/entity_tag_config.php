<?php
    $tagResult = $result = civicrm_api3('Tag', 'get', array(
        'sequential' => 1,
    ));
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

<h2>Tag(s)</h2>
<div class="caldera-config-group caldera-config-group-full">
    <div class="caldera-config-field">
        <?php foreach ($tagResult['values'] as $key => $value) { ?>
        <label><input id="entity_tag" type="checkbox" name="{{_name}}[entity_tag_<?php echo $value['id']; ?>]" value="<?php echo $value['id']; ?>" {{#if entity_tag_<?php echo $value['id']; ?>}}checked="checked"{{/if}}><?php echo $value['name']; ?></label>
        <?php } ?>
    </div>
</div>
