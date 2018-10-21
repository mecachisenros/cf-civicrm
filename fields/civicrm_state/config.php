<?php $state = caldera_forms_civicrm()->helper->get_state_province(); ?>
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
		<?php foreach( $state as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['name'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<div class="caldera-config-group">
	<label for="{{_id}}_default"><?php esc_html_e('CiviCRM Country field'); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_civicrm_country" class="block-input field-config magic-tag-enabled" name="{{_name}}[civicrm_country]" value="{{civicrm_country}}">
		<p class="description">The CiviCRM Country field <strong>slug</strong> for which the states should sync (needed when more than one Country/State fields are present in a form).</p>
	</div>
</div>

{{#script}}
	if('{{default}}'){
		jQuery('#{{_id}}_default').val('{{default}}');
	}
{{/script}}
