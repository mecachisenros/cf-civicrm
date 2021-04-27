<?php $counties = caldera_forms_civicrm()->helper->get_counties(); ?>
<div class="caldera-config-group">
	<label><?php _e( 'Placeholder', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_placeholder" class="block-input field-config" name="{{_name}}[placeholder]" value="{{placeholder}}">
	</div>
</div>
	<div class="caldera-config-group">
	<label><?php _e( 'Default', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_default" class="block-input field-config" name="{{_name}}[default]" value="{{default}}">
		<option value="" {{#is default value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $counties as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['name'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<div class="caldera-config-group">
	<label for="{{_id}}_default"><?php esc_html_e('CiviCRM State/Province field'); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_civicrm_state_province" class="block-input field-config magic-tag-enabled" name="{{_name}}[civicrm_state_province]" value="{{civicrm_state_province}}">
		<p class="description">The CiviCRM State/Province field <strong>slug</strong> for which the counties should sync (needed when more than one State/Province fields are present in a form).</p>
	</div>
</div>

{{#script}}
	if('{{default}}'){
		jQuery('#{{_id}}_default').val('{{default}}');
	}
{{/script}}
