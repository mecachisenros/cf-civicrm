<?php $countries = caldera_forms_civicrm()->helper->get_countries(); ?>
<div class="caldera-config-group">
	<label><?php _e( 'Placeholder', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_placeholder" class="block-input field-config" name="{{_name}}[placeholder]" value="{{placeholder}}">
	</div>
</div>
<div class="caldera-config-group">
    <label><?php _e( 'Default', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_default" class="block-input field-config" name="{{_name}}[default]" value="{{default}}">
        <option value="" {{#is default value=""}}selected="selected"{{/is}}></option>
        <?php foreach( $countries['values'] as $key => $value ) { ?>
            <option value="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['name'] ); ?></option>
        <?php } ?>
        </select>
	</div>
</div>
{{#script}}
    if('{{default}}'){
        jQuery('#{{_id}}_default').val('{{default}}');
    }
{{/script}}
