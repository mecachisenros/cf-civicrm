<div class="caldera-config-group">
	<div class="caldera-config-field">
		<label for="{{_id}}_pre_render">
			<input id="{{_id}}_pre_render" type="checkbox" class="field-config" name="{{_name}}[pre_render]" {{#if pre_render}}checked="checked"{{/if}} value="1">
			<?php _e( 'Pre render CiviCRM data for this field', 'caldera-forms-civicrm' ); ?>
		</label>
	</div>
	<p class="description">
		<?php _e( 'CiviCRM data mapped to hidden fields is ignored by default, check this option to enable auto population of CiviCRM data.', 'caldera-forms-civicrm' ); ?>
	</p>
</div>