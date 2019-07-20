<?php
/**
 * The processor config template.
 *
 * This is used to capture configuration settings to be used for the form processor.
 * Form Configs are not traditional POST config saves. the whole form config object
 * is built in javascript and the object posted to be saved.
 *
 * This means that all config fields need to have the have the '.field-config'
 * and the name="{{_name}}[config_name]" attribute class in order to be included
 * in the config.
 *
 * Additional Tags:
 *
 * {{_id}} : unique field ID
 * {{{_field config_name type="field_type" required="true"}}} : Creates a direct bound field select - NOTE the triple {{{
 * {{#script}} ... {{/script}} : inline javascript wrapper for dynamically adding javascript to the template.
 * {{#is config_name value="check_value"}} do this {{/is}} : condition check of value ( for checks, selects etc. )
 *
 * CSS class names:
 *
 * .magic-tag-enabled 		: class to set an input / textarea to be magic tag enabled (tags auto complete)
 * .block-input				: full width input
 * .field-config			: required to set the field as a config setting
 * .caldera-config-group	: field wrapper class for styling
 * .caldera-config-field	: field inner wrapper for styling
 * .required				: class name to specify the field is required
 *
 * @see https://github.com/Desertsnowman/cf-formprocessor-boilerplate
 *
 * @since 0.2
 */

?>
<div class="caldera-config-group">
	<label for="{{_id}}_first_option"><?php _e( 'My Config Input', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_first_option" class="block-input field-config magic-tag-enabled required" name="{{_name}}[first_option]" value="{{first_option}}">
		<p><?php _e( 'This field is magic tag enabled and required', 'cf-civicrm' ); ?></p>
	</div>
</div>

<div class="caldera-config-group">
	<label for="{{_id}}_second_option"><?php _e( 'My Bound Input', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="second_option" type="text" required="true"}}}
		<p><?php _e( 'This field is a field bound input and is set as required', 'cf-civicrm' ); ?></p>
	</div>
</div>

<div class="caldera-config-group">
	<label for="{{_id}}_third_option"><?php _e( 'My Optional Input', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_third_option" class="block-input field-config" name="{{_name}}[third_option]" value="{{third_option}}">
		<p><?php _e( 'This field is a field not required', 'cf-civicrm' ); ?></p>
	</div>
</div>

<div class="caldera-config-group">
	<label for="{{_id}}_third_option"><?php _e( 'My Optional Email', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="third_option" type="email"}}}
		<p><?php _e( 'This is a bound field and is not required and bound to "email" field types only', 'cf-civicrm' ); ?></p>
	</div>
</div>
