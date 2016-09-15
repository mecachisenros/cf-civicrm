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
        $country = civicrm_api3('Country', 'get', array(
    				'sequential' => 1,
    				'return' => array("id", "name"),
    				'options' => array('limit' => 0),
					'id' => array('IN' => CiviCRM_Caldera_Forms_Helper::get_civicrm_settings('countryLimit')),
				));
        foreach( $country['values'] as $key => $value) { ?>
            <option value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
        <?php } ?>
        </select>
	</div>
</div>
{{#script}}
    if('{{default}}'){
        jQuery('#{{_id}}_default').val('{{default}}');
    }
{{/script}}
