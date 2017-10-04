<?php

$financial_types = civicrm_api3( 'Contribution', 'getoptions', array(
	'sequential' => 1,
	'field' => 'financial_type_id',
));

?>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<!-- Contact ID -->
<h2><?php _e( 'Required Contribution Fields', 'caldera-forms-civicrm' ); ?></h2>

<!-- Financial Type -->
<div id="financial_type" class="caldera-config-group">
	<label><?php _e( 'Financial Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[financial_type]" required>
		<?php foreach ( $financial_types['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['value'] ); ?>" {{#is financial_type value="<?php echo $value['value']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>


<!-- Currency -->
<div id="currency" class="caldera-config-group">
	<label><?php _e( 'Currency', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php echo '{{{_field slug="currency" exclude="system"}}}'; ?>
	</div>
</div>

<!-- Amount Field -->
<div id="amount" class="caldera-config-group">
	<label><?php _e( 'Amount Field', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php echo '{{{_field slug="amount" exclude="system" required="true"}}}'; ?>
	</div>
</div>

<h2><?php _e( 'Other Contribution Fields', 'caldera-forms-civicrm' ); ?></h2>
<!-- Source Field -->
<div id="source" class="caldera-config-group">
	<label><?php _e( 'Source Field', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php echo '{{{_field slug="source" exclude="system"}}}'; ?>
	</div>
</div>

<div id="transaction_id" class="caldera-config-group">
	<label><?php _e( 'Transaction ID', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php echo '{{{_field slug="transaction_id" exclude="system"}}}'; ?>
	</div>
</div>
