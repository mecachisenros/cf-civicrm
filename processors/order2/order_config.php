<?php

$financial_types = civicrm_api3( 'FinancialType', 'get', array(
	'sequential' => 1,
	'is_active' => 1,
));

$contribution_status = civicrm_api3( 'Contribution', 'getoptions', array(
  'sequential' => 1,
  'field' => 'contribution_status_id',
));

$price_sets = caldera_forms_civicrm()->helper->get_price_sets();

?>

<p><strong><?php _e( 'Note:', 'caldera-forms-civicrm' ); ?></strong> <?php _e( 'This processor does not process payment transactions on it\'s own, it just creates a Contribution in CiviCRM with single or multiple line items. In order to process live payment transaction a Caldera Forms add-on is needed.', 'caldera-forms-civicrm' ); ?></p>

<!-- Contact ID -->
<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<!-- Order Fields -->
<h2><?php _e( 'Order Fields', 'caldera-forms-civicrm' ); ?></h2>
<!-- Financial Type -->
<div id="financial_type_id" class="caldera-config-group">
	<label><?php _e( 'Financial Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[financial_type_id]">
		<?php foreach ( $financial_types['values'] as $key => $financial_type ) { ?>
			<option value="<?php echo esc_attr( $financial_type['id'] ); ?>" {{#is financial_type_id value=<?php echo $financial_type['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $financial_type['name'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Contribution Status -->
<div id="contribution_status_id" class="caldera-config-group">
	<label><?php _e( 'Contribution Status', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contribution_status_id]">
		<?php foreach ( $contribution_status['values'] as $key => $status ) { ?>
			<option value="<?php echo esc_attr( $status['key'] ); ?>" {{#is contribution_status_id value=<?php echo $status['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $status['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Recieve Date -->
<div id="receive_date" class="caldera-config-group">
	<label><?php _e( 'Receive Date', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="receive_date"}}}
	</div>
</div>

<!-- Total Amount -->
<div id="total_amount" class="caldera-config-group">
	<label><?php _e( 'Total Amount', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="total_amount"}}}
	</div>
</div>
<hr style="clear: both;" />

<!-- Line Items -->
<h1><?php _e( 'Line Items', 'caldera-forms-civicrm' ); ?></h1>

<div id="{{_id}}_line_items">
    <?php for ( $num = 1; $num < 11; $num++ ) { ?>
    <div id="line-item-<?php esc_attr( $num ); ?>" class="line-item caldera-config-group">
		<label><?php _e( 'Line Item ' . $num, 'caldera-forms-civicrm' ); ?></label>
		<div class="caldera-config-field">
			<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[line_items][line_item_<?php echo $num; ?>]" value="{{<?php echo 'line_items/line_item_' . $num; ?>}}">
		</div>
	</div>
	<?php } ?>
</div>

<script>
var cfc_line_items;


jQuery( function( $ ){
	function get_base_form(){
		return $('.caldera-forms-options-form').formJSON();
	}
	console.log( get_base_form() );

});

</script>