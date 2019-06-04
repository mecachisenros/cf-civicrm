<?php

$fields = civicrm_api3( 'Membership', 'getfields', [
	'sequential' => 1,
	'action' => 'create',
] );

$membership_fields = [];
foreach ( $fields['values'] as $key => $value ) {
	$membership_fields[$value['name']] = $value['title'];
}

$ignore = [ 'membership_type_id', 'contact_id', 'is_test', 'status_id', 'is_override', 'status_override_end_date', 'owner_membership_id', 'max_related', 'contribution_recur_id', 'id', 'is_pay_later', 'skipStatusCal' ];

$current_fields = [ 'source' ];

$membership_types = civicrm_api3( 'MembershipType', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'visibility' => 'Public',
	'options' => [ 'limit' => 0 ],
] );

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_is_monetary" type="checkbox" name="{{_name}}[is_monetary]" value="1" {{#if is_monetary}}checked="checked"{{/if}}><?php _e( 'Is a paid for membership.', 'cf-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_is_renewal" type="checkbox" name="{{_name}}[is_renewal]" value="1" {{#if is_renewal}}checked="checked"{{/if}}><?php _e( 'Extend/renew this membership if the Contact has a membership of the same type.', 'cf-civicrm' ); ?></label>
	</div>
</div>

<div id="{{_id}}_preserve_join_date" class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input class="preserve_join_date" type="checkbox" name="{{_name}}[preserve_join_date]" value="1" {{#if preserve_join_date}}checked="checked"{{/if}}><?php _e( 'Preserve membership join date (Member since) for any oldest membership belonging to Organization.', 'cf-civicrm' ); ?></label>
	</div>
	<div class="member_of_contact_id caldera-config-group">
		<label><?php _e( 'Organization', 'cf-civicrm' ); ?></label>
		<div class="caldera-config-field">
			<select id="{{_id}}_member_of_contact_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[member_of_contact_id]">
			</select>
		</div>
	</div>
	<div class="is_membership_type caldera-config-group">
		<input type="checkbox" name="{{_name}}[is_membership_type]" value="1" {{#if is_membership_type}}checked="checked"{{/if}}><?php _e( 'Membership type based.', 'cf-civicrm' ); ?>
	</div>
</div>

<?php do_action( 'cfc_membership_processor_config_template_before_link', $element ); ?>

<hr style="clear: both;" />

<h2><?php _e( 'Contact Link', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
	</div>
</div>

<!-- Membership type -->
<div id="{{_id}}_membership_type_id" class="caldera-config-group">
	<label><?php _e( 'Membership Type', 'cf-civicrm' ); ?></label>
	<div class="membership_type_id caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[membership_type_id]">
			<option value=""><?php _e( 'Select a Membership', 'cf-civicrm' ); ?></option>
		<?php foreach ( $membership_types['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is membership_type_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['name'] ); ?></option>
		<?php } ?>
		</select>
	</div>
	<div class="is_price_field_based caldera-config-field">
		<label><input type="checkbox" name="{{_name}}[is_price_field_based]" value="1" {{#if is_price_field_based}}checked="checked"{{/if}}><?php _e( 'Use Price Field based Membership Type.', 'cf-civicrm' ); ?></label>
	</div>
	<div class="price_field_value">
		<label><?php _e('Price Field Value', 'cf-civicrm');?></label>
		<div class="price_field_value caldera-config-field">
			<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" name="{{_name}}[price_field_value]" value="{{price_field_value}}">
		</div>
	</div>
</div>

<hr style="clear: both;" />

<!-- Membership fields -->
<h2><?php _e( 'Membership Fields', 'cf-civicrm' ); ?></h2>
<?php foreach ( $membership_fields as $key => $value ) {
		if( ! in_array( $key, $ignore ) ) { ?>
	<div id="{{_id}}_<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>

<script>
	( function() {
		var prId = '{{_id}}',
		membership_type = '#' + prId + '_membership_type_id',
		preserve_join_date = '#' + prId + '_preserve_join_date';

		$( membership_type + ' .is_price_field_based input' ).on( 'change', function( i, el ) {
			var is_price_filed_based = $( this ).prop( 'checked' );
			$( '.membership_type_id', $( membership_type ) ).toggle( ! is_price_filed_based );
			$( '.price_field_value', $( membership_type ) ).toggle( is_price_filed_based );
			is_price_filed_based ? $( membership_type + ' select' ).removeClass( 'required' ) : $( membership_type + ' select' ).addClass( 'required' );
		} ).trigger( 'change' );

		$( preserve_join_date + ' input.preserve_join_date' ).on( 'change', function( i, el ) {
			var is_checked = $( this ).prop( 'checked' );
			$( '.member_of_contact_id', $( preserve_join_date ) ).toggle( is_checked );
			$( '.is_membership_type', $( preserve_join_date ) ).toggle( is_checked );
		} ).trigger( 'change' );

		cfc_select2_defaults( '#{{_id}}_member_of_contact_id', '{{member_of_contact_id}}' );

	} )();
</script>
