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
<?php
$price_sets = caldera_forms_civicrm()->helper->get_price_sets();
$used_for = array(
	'civicrm_participant' => __( 'CiviCRM Participant', 'caldera-forms-civicrm' ),
	'civicrm_membership' => __( 'CiviCRM Membership', 'caldera-forms-civicrm' ),
	'civicrm_contribution' => __( 'CiviCRM Contribution', 'caldera-forms-civicrm' ),
);
?>
<div id="{{_id}}_line_items">
	<?php for ( $item = 1; $item < 11;  $item++ ) { ?>
	<div class="{{_id}}_cfc_line_item_<?php echo $item; ?> line-item" data-item-number="<?php echo $item; ?>">
		<a id="line-item-<?php echo $item; ?>-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Line Item ' . $item, 'caldera-forms-civicrm' ); ?></a>

		<div class="line-item-<?php echo $item; ?>-fields" style="display: none;">
			<div class="caldera-config-group entity_table">
				<label><?php _e( 'Entity Table', 'caldera-forms-civicrm' ); ?></label>
				<select class="caldera-config-field required" name="{{_name}}[line_item][<?php echo $item; ?>][entity_table]">
						<option value="" {{#is line_item/<?php echo $item; ?>/entity_table value=""}}selected="selected"{{/is}}></option>
					<?php foreach ( $used_for as $entity => $entity_name ) { ?>
						<option value="<?php echo esc_attr( $entity ); ?>" {{#is line_item/<?php echo $item; ?>/entity_table value="<?php echo $entity; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $entity_name ); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="caldera-config-group contact_link">
				<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
				<div class="caldera-config-field">
					<?php caldera_forms_civicrm()->helper->contact_link_order_field( $item ); ?>
				</div>
			</div>

			<!-- <div class="caldera-config-group entity_params">
				<label><?php _e( 'Entity Data', 'caldera-forms-civicrm' ); ?></label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[line_item][<?php echo $item; ?>][entity_params]" value="{{line_item/<?php echo $item; ?>/entity_params}}">
				</div>
			</div> -->

			<div class="caldera-config-group price_field_value">
				<label><?php _e( 'Price Field', 'caldera-forms-civicrm' ); ?></label>
				<div class="binded_price_field caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[line_item][<?php echo $item; ?>][price_field_value]" value="{{line_item/<?php echo $item; ?>/price_field_value}}">
				</div>
				<div class="{{_id}}_fixed_price_field_<?php echo $item; ?> is_fixed caldera-config-field">
					<label><input type="checkbox" name="{{_name}}[line_item][<?php echo $item; ?>][is_fixed_price_field]" value="1" {{#if line_item/<?php echo $item; ?>/is_fixed_price_field}}checked="checked"{{/if}}><?php _e( 'Use a fixed Price Field', 'caldera-forms-civicrm' ); ?></label>
				</div>
				<div class="fixed_price_field caldera-config-field">
					<select class="block-input field-config" name="{{_name}}[line_item][<?php echo $item; ?>][fixed_price_field_value]">
						<option value=""><?php _e( 'Select a Price Field', 'caldera-forms-civicrm' ); ?></option>
						<?php 
							foreach ( $price_sets as $price_set_id => $price_set ) {
								echo '<optgroup label="'.$price_set['title'].'">';
								foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
									echo '<optgroup label="'.$price_field['label'].'">';
									foreach (  $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) {
										echo '<option value="'.esc_attr( $price_field_value_id ).'" {{#is line_item/'.$item.'/fixed_price_field_value value='.$price_field_value_id.'}}selected="selected"{{/is}}>'.esc_html( $price_field_value['label'] ).'</option>';
									}
									echo '</optgroup>';
								}
								echo '</optgroup>';
							}
						?>
						<!-- <?php foreach ( caldera_forms_civicrm()->helper->get_price_sets() as $price_set_id => $price_set ) { 
							foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) { 
								foreach ( $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) { ?>
								<option value="<?php echo esc_attr( $price_field_value_id ); ?>" {{#is line_item/<?php echo $item; ?>/fixed_price_field_value value=<?php echo $price_field_value_id; ?>}}selected="selected"{{/is}}><?php echo esc_html( 'CiviCRM Price Field - ' . $price_set['title'] . ' - ' . $price_field_value['label'] ); ?></option>
						<?php } } } ?> -->
					</select>
				</div>
			</div>

			<!-- <div class="caldera-config-group quantity">
				<label><?php _e( 'Quantity', 'caldera-forms-civicrm' ); ?></label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[line_item][<?php echo $item; ?>][qty]" value="{{line_item/<?php echo $item; ?>/qty}}">
				</div>
			</div> -->

		</div>
	</div>
	<?php } ?>
</div>

<script>

	(function(){
		var prId = "{{_id}}",
		prContainer = '#' + prId + '_settings_pane .caldera-config-processor-setup';

		$( prContainer + ' .civicrm-accordion' ).each( function( i, el ){
			$( this ).click( function(){
				$( prContainer + ' .' + $( this ).attr('id').replace( '-button', '' ) ).toggle('slow');
				$( this ).toggleClass('button-primary');
			})
		});

		$('.line-item').map( function( i, el ){
			var item = $( this );
			$( '.is_fixed input', this ).on( 'change', function() { 
				var is_fixed = $( this ).prop( 'checked' );
				$( '.binded_price_field', item ).toggle( ! is_fixed );
				$( '.fixed_price_field', item ).toggle( is_fixed );
			}).trigger('change');
		});
	})();

</script>