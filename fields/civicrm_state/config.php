<div class="caldera-config-group">
	<label><?php echo 'Placeholder'; ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_placeholder" class="block-input field-config" name="{{_name}}[placeholder]" value="{{placeholder}}">
	</div>
</div>
<div class="caldera-config-group">
    <label><?php _e('Default'); ?></label>
    <div class="caldera-config-field">
        <select id="{{_id}}_default" class="block-input field-config" name="{{_name}}[default]" value="{{default}}">
        <option value="" {{#is default value=""}}selected="selected"{{/is}}></option>
        <?php
        $state = CiviCRM_Caldera_Forms_Helper::get_state_province();
        foreach( $state as $key => $value) { ?>
            <option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>
{{#script}}
    if('{{default}}'){
        jQuery('#{{_id}}_default').val('{{default}}');
    }
{{/script}}
