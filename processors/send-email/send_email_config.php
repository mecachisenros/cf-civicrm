<?php

$messageTemplates = civicrm_api3( 'MessageTemplate', 'get', [
	'sequential' => 1,
	'return' => [ 'id', 'msg_title', 'msg_subject' ],
	'workflow_id' => [ 'IS NULL' => 1 ],
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

?>

<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<div id="{{_id}}_template_id" class="caldera-config-group">
	<label><?php _e('Message Template', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[template_id]">
		<?php foreach( $messageTemplates['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is template_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['msg_title'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<p><em><?php echo _e( 'From Name, From Email, and Alterantive Email Receiver fields are optional.' ); ?></em></p>

<div id="{{_id}}_from_name" class="caldera-config-group">
	<label><?php echo _e( 'From Name', 'cf-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[from_name]" value="{{from_name}}">
	</div>
</div>

<div id="{{_id}}_form_email" class="caldera-config-group">
	<label><?php echo _e( 'From Email', 'cf-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[from_email]" value="{{from_email}}">
	</div>
</div>

<div id="{{_id}}_alternative_receiver_address" class="caldera-config-group">
	<label><?php echo _e( 'Alternative Email Receiver', 'cf-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[alternative_receiver_address]" value="{{alternative_receiver_address}}">
	</div>
</div>
