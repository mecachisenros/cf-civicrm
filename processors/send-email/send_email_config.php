<?php

$messageTemplates = civicrm_api3('MessageTemplate', 'get', array(
	'sequential' => 1,
	'return' => array("id", "msg_title", "msg_subject"),
	'workflow_id' => array('IS NULL' => 1),
	'is_active' => 1,
	'options' => array('limit' => 0),
));

?>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />
<?php

if( function_exists('civicrm_api3_email_send')){
	echo "Yes";
} else {
	echo "No";
}

?>

<div class="caldera-config-group">
	<label><?php _e('Message Template', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[template_id]">
		<?php foreach( $messageTemplates['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is template_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['msg_title'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<p><em><?php echo _e( 'From Name, From Email, and Alterantive Email Receiver fields are optional.' ); ?></em></p>

<div class="caldera-config-group">
	<label><?php echo _e( 'From Name', 'caldera-forms-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[from_name]" value="{{from_name}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php echo _e( 'From Email', 'caldera-forms-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[from_email]" value="{{from_email}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php echo _e( 'Alternative Email Receiver', 'caldera-forms-civicrm'); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[alternative_receiver_address]" value="{{alternative_receiver_address}}">
	</div>
</div>
