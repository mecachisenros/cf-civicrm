<?php

$fields = civicrm_api3( 'Participant', 'getfields', [
	'sequential' => 1,
	'action' => 'create',
] );

$participant_roles = civicrm_api3( 'Participant', 'getoptions', [
	'sequential' => 1,
	'field' => 'participant_role_id',
] );

$participant_statuses = civicrm_api3( 'Participant', 'getoptions', [
	'sequential' => 1,
	'field' => 'participant_status_id',
] );

$participant_fields = [];
foreach ( $fields['values'] as $key => $value ) {
	$participant_fields[$value['name']] = $value['title'];	
}

$ignore = [ 'event_id', 'contact_id', 'is_test', 'discount_amount', 'cart_id', 'must_wait', 'transferred_to_contact_id', 'id', 'status_id', 'role_id', 'register_date', 'fee_level', 'is_pay_later', 'fee_amount', 'register_by_id', 'discount_id', 'fee_currency', 'campaign_id' ];

$current_fields = [ 'source' ];

$events = civicrm_api3( 'Event', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'is_online_registration' => 1,
	'is_template' => 0,
	'options' => [ 'limit' => 0 ],
] );

$campaigns = civicrm_api3( 'Campaign', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_is_monetary" type="checkbox" name="{{_name}}[is_monetary]" value="1" {{#if is_monetary}}checked="checked"{{/if}}><?php _e( 'Is a paid for event.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_is_email_receipt" type="checkbox" name="{{_name}}[is_email_receipt]" value="1" {{#if is_email_receipt}}checked="checked"{{/if}}><?php _e( 'Email receipt.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<div class="caldera-config-group caldera-config-group-full {{_id}}_disable_all_fields">
	<div class="caldera-config-field">
		<label>
			<input id="{{_id}}_disable_all_fields" type="checkbox" name="{{_name}}[disable_all_fields]" value="1" {{#if disable_all_fields}}checked="checked"{{/if}}><?php _e( 'Disable form\'s fields options if the contact is registered for this event.', 'caldera-forms-civicrm' ); ?>
		</label>
		<p class="description">
			<?php sprintf( _e( 'When a participant is registered for the event linked to this Participant processor, a notice will be displayed informing the user that they already registered. By checking this setting we will disable all other fields and field options as well. This is particularly useful when offering multiple events on a single form, and if one is registered for the event linked to this processor, you want to disable not only registering for this event but for any other events or options on the form.  This is typically done when one event is considered as <em>main event</em>.', 'caldera-forms-civicrm' ) ); ?>
		</p>
	</div>
</div>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<!-- Event -->
<div id="{{_id}}_event_id" class="caldera-config-group">
	<label><?php _e( 'Event', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[id]">
			<option value=""><?php _e( 'Select an Event', 'caldera-forms-civicrm' ); ?></option>
		<?php foreach ( $events['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}} data-default-role-id="<?php echo esc_attr( $value['default_role_id'] ); ?>" data-event-type-id="<?php echo esc_attr( $value['event_type_id'] ); ?>" ><?php echo esc_html( $value['title'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Participant Role -->
<div id="{{_id}}_participant_role_id" class="caldera-config-group">
	<label><?php _e( 'Participant Role', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[role_id]">
			<option value="default_role_id" {{#is role_id value="default_role_id"}}selected="selected"{{/is}}><?php _e( 'Event Default Role', 'caldera-forms-civicrm' ); ?></option>
		<?php foreach ( $participant_roles['values'] as $key => $role ) { ?>
			<option value="<?php echo esc_attr( $role['key'] ); ?>" {{#is role_id value=<?php echo $role['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $role['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Participant Status -->
<div id="{{_id}}_status_id" class="caldera-config-group">
	<label><?php _e( 'Participant Status', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[status_id]">
			<option value="default_status_id" {{#is status_id value="default_role_id"}}selected="selected"{{/is}}><?php _e( 'Event Default Status', 'caldera-forms-civicrm' ); ?></option>
		<?php foreach ( $participant_statuses['values'] as $key => $status ) { ?>
			<option value="<?php echo esc_attr( $status['key'] ); ?>" {{#is status_id value=<?php echo $status['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $status['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Campaign -->
<div id="campaign_id" class="caldera-config-group">
	<label><?php _e( 'Campaign', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[campaign_id]">
		<option value="" {{#is campaign_id value=""}}selected="selected"{{/is}}></option>
		<?php foreach ( $campaigns['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is campaign_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['title'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<hr style="clear: both;" />

<!-- Participant fields -->
<h2><?php _e( 'Participant Fields', 'caldera-forms-civicrm' ); ?></h2>
<?php foreach ( $participant_fields as $key => $value ) { 
	if( in_array( $key, $current_fields ) ) {
	?>
	<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>

<h2><?php _e( 'Custom Fields', 'caldera-forms-civicrm' ); ?></h2>
<?php foreach ( caldera_forms_civicrm()->helper->get_participant_custom_fields() as $key => $custom_field ) { ?>
	<div 
		id="{{_id}}_<?php echo esc_attr( $key ); ?>" 
		class="caldera-config-group" 
		data-entity-column-id="<?php echo esc_attr( $custom_field['extends_entity_column_id'] ); ?>"
		data-entity-column-value="<?php echo esc_attr( json_encode( $custom_field['extends_entity_column_value'] ) ); ?>"
		>
		<label><?php echo esc_html( $custom_field['label'] ); ?> </label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } ?>

<script>
	$('#{{_id}}_event_id select').on( 'change', function() {
		var event_id = $( this ).val(),
		event_type_id = $( 'option:selected', this ).data( 'event-type-id' ),
		role_id = $( 'option:selected', this ).data( 'default-role-id' );

		$( '[id^={{_id}}_custom]' ).map( function( i, el ) {
			if ( $( this ).data( 'entity-column-value' ) != undefined ) {

				var column_value = $( el ).data( 'entity-column-value' ).toString(),
				column_id = $( el ).data( 'entity-column-id' );

				if ( column_id == 3 || column_id == 2 ) {
					if( column_value.indexOf( event_id ) !== -1 
						|| column_value.indexOf( event_type_id ) !== -1 
						|| column_value.indexOf( role_id ) !== -1 ) {
						$( el ).show()
					} else {
						$( el ).hide();
					}
				}
			}
		} ).trigger( 'change' );
	} );


	$( '#{{_id}}_is_monetary' ).on( 'change', function( e ) {
		$( '.{{_id}}_disable_all_fields' ).toggle( $( this ).prop( 'checked' ) );
	} ).trigger( 'change' );

</script>
