<?php

$fields = civicrm_api3( 'Membership', 'getfields', array(
	'sequential' => 1,
	'action' => 'create',
));

$membership_fields = array();
foreach ( $fields['values'] as $key => $value ) {
	$membership_fields[$value['name']] = $value['title'];	
}

$ignore = array( 'membership_type_id', 'contact_id', 'is_test', 'status_id', 'is_override', 'status_override_end_date', 'owner_membership_id', 'max_related', 'contribution_recur_id', 'id', 'is_pay_later', 'skipStatusCal' );

$current_fields = array( 'source' );

$membership_types = civicrm_api3( 'MembershipType', 'get', array(
	'sequential' => 1,
    'is_active' => 1,
    'visibility' => 'Public',
	'options' => array( 'limit' => 0 ),
));

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="is_monetary" type="checkbox" name="{{_name}}[is_monetary]" value="1" {{#if is_monetary}}checked="checked"{{/if}}><?php _e( 'Is a paid for membership.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="is_renewal" type="checkbox" name="{{_name}}[is_renewal]" value="1" {{#if is_renewal}}checked="checked"{{/if}}><?php _e( 'Extend/renew this membership if the Contact has a membership of the same type.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<hr style="clear: both;" />

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<!-- Membership type -->
<div id="{{_id}}_membership_type_id" class="caldera-config-group">
	<label><?php _e( 'Membership Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[membership_type_id]">
			<option value=""><?php _e( 'Select a Membership', 'caldera-forms-civicrm' ); ?></option>
		<?php foreach ( $membership_types['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is membership_type_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['name'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Price Field -->
<!-- <div id="{{_id}}_price_field" class="caldera-config-group">
	<label><?php _e( 'Price Field', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[price_field_id]">
			<option value=""><?php _e( 'Select a Price Field', 'caldera-forms-civicrm' ); ?></option>
		    <?php foreach ( caldera_forms_civicrm()->helper->get_price_sets() as $price_set_id => $price_set ) { 
				    foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) { ?>
			        <option value="<?php echo esc_attr( $price_field_id ); ?>" {{#is price_field_id value=<?php echo $price_field_id; ?>}}selected="selected"{{/is}}><?php echo esc_html( 'CiviCRM Price Field - ' . $price_set['title'] . ' - ' . $price_field['label'] ); ?></option>
		    <?php } } ?>
		</select>
	</div>
</div> -->

<hr style="clear: both;" />

<!-- Membership fields -->
<h2><?php _e( 'Membership Fields', 'caldera-forms-civicrm' ); ?></h2>
<?php foreach ( $membership_fields as $key => $value ) { 
        if( ! in_array( $key, $ignore ) ) { ?>
	<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>