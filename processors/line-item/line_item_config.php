<?php

$entities = [
	'civicrm_participant' => __( 'CiviCRM Participant', 'caldera-forms-civicrm' ),
	'civicrm_membership' => __( 'CiviCRM Membership', 'caldera-forms-civicrm' ),
	'civicrm_contribution' => __( 'CiviCRM Contribution', 'caldera-forms-civicrm' ),
];

$membership_types = civicrm_api3( 'MembershipType', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'visibility' => 'Public',
	'options' => [ 'limit' => 0 ],
] );

$fields = civicrm_api3( 'LineItem', 'getfields', [
	'api_action' => 'create',
] );

$price_sets = caldera_forms_civicrm()->helper->cached_price_sets();

?>

<!-- Entity Table -->
<div id="{{_id}}_entity_table" class="caldera-config-group entity">
	<label><?php _e( 'Entity Table', 'caldera-forms-civicrm' );?></label>
		<select class="caldera-config-field" name="{{_name}}[entity_table]">
			<option value="" {{#is entity_table value=""}}selected="selected"{{/is}}></option>
			<?php foreach ( $entities as $entity => $entity_name ) { ?>
				<option value="<?php echo esc_attr( $entity ); ?>" {{#is entity_table value="<?php echo $entity; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $entity_name ); ?></option>
			<?php } ?>
	</select>
</div>

<!-- Entity Data -->
<div id="{{_id}}_entity_data" class="entity-data caldera-config-group">
	<label><?php _e( 'Entity Data (Membership or Participant)', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[entity_params]" value="{{entity_params}}">
		<p class="description"><?php sprintf( _e( 'Required when the \'Entity Table\' setting is set to CiviCRM Participant or CiviCRM Membership, optional for CiviCRM Contribution.<br>When \'Entity Table\' is set to CiviCRM Contribution, set the Participant processor magic tag if this is a Contribution Line Item associated to a particular Participant, like for example a Donation.', 'caldera-forms-civicrm') );?></p>
	</div>
</div>

<!-- Price Field Value -->
<div id="{{_id}}_price_field_value" class="caldera-config-group">
	<label><?php _e( 'Price Field Value', 'caldera-forms-civicrm' );?></label>
	<div class="binded_price_field caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[price_field_value]" value="{{price_field_value}}">
	</div>
	<div class="is_fixed caldera-config-field">
		<label><input type="checkbox" name="{{_name}}[is_fixed_price_field]" value="1" {{#if is_fixed_price_field}}checked="checked"{{/if}}><?php _e( 'Use a fixed Price Field Option', 'caldera-forms-civicrm' ); ?></label>
	</div>
	<div class="fixed_price_field caldera-config-field">
	<?php if ( $price_sets ): ?>
		<select class="block-input field-config" name="{{_name}}[fixed_price_field_value]">
			<option value=""><?php _e( 'Select a Price Field', 'caldera-forms-civicrm' ); ?></option>
			<?php
				foreach ( $price_sets as $price_set_id => $price_set ) {
					foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
						echo '<optgroup label="CiviCRM Price Set:' . $price_set['title'] . ' - Price Field:' . $price_field['label'] . '">';
						foreach ( $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) {
							echo '<option value="' . esc_attr( $price_field_value_id ) . '" {{#is fixed_price_field_value value=' . $price_field_value_id . '}}selected="selected"{{/is}}>' . esc_html( $price_field_value['label'] ) . '</option>';
						}
						echo '</optgroup>';
					}
				}
				?>
		</select>
		<?php else: ?>
		<div class="field-config"><?php _e( 'No price sets.', 'caldera-forms-civicrm' ); ?></div>
		<?php endif; ?>
	</div>
</div>

<!-- Quantity -->
<div id="{{_id}}_qty" class="entity-data caldera-config-group">
	<label><?php _e( 'Quantity', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[qty]" value="{{qty}}">
	</div>
</div>

<!-- Use qty as count -->
<div id="{{_id}}_use_qty_as_count" class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input type="checkbox" name="{{_name}}[use_qty_as_count]" value="1" {{#if use_qty_as_count}}checked="checked"{{/if}}><?php _e( 'Use quantity as participant head count', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div id="{{_id}}_other_amount_wrapper">
	<!-- Is Other Amount -->
	<div id="{{_id}}_is_other_amount" class="caldera-config-group caldera-config-group-full">
		<div class="is_other_amount caldera-config-field">
			<label><input type="checkbox" name="{{_name}}[is_other_amount]" value="1" {{#if is_other_amount}}checked="checked"{{/if}}><?php _e( 'Is Other Amount. (check this field to enable Other Amount)', 'caldera-forms-civicrm' ); ?></label>
		</div>        
	</div>

	<!-- Amount -->
	<div id="{{_id}}_amount" class="caldera-config-group">
		<label><?php _e( 'Other Amount', 'caldera-forms-civicrm' );?></label>
		<div class="caldera-config-field">
			<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[amount]" value="{{amount}}">
		<p><?php _e( 'Use this field for Other Amount', 'caldera-forms-civicrm');?></p>
		</div>
	</div>
</div>

<script>
	( function() {
		var prId = '{{_id}}',
		price_field_value = '#' + prId + '_price_field_value',
		entity_table = '#' + prId + '_entity_table',
		entity_data = '#' + prId + '_entity_data',
		amount_wrapper = '#' + prId + '_other_amount_wrapper',
		is_other_amount = '#' + prId + '_is_other_amount',
		amount = '#' + prId + '_amount';

		$( price_field_value + ' .is_fixed input' ).on( 'change', function( i, el ) {
			var is_fixed = $( this ).prop( 'checked' );
			$( '.binded_price_field', $( price_field_value ) ).toggle( ! is_fixed );
			$( '.fixed_price_field', $( price_field_value ) ).toggle( is_fixed );
			// qty section
			var condition = $( entity_table + ' select' ).val() == 'civicrm_participant' && is_fixed;
			$( '#' + prId + '_qty' ).toggle( condition );
			$( '#' + prId + '_use_qty_as_count' ).toggle( condition );
		} ).trigger( 'change' );

		$( is_other_amount + ' input' ).on( 'change', function( i, el ) {
			var checked = $( this ).prop( 'checked' );
			$( amount ).toggle( checked );
		} ).trigger( 'change' );

	} )();
</script>