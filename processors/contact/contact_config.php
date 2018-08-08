<div class="civicrm-entity-fields">
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="auto_pop" type="checkbox" name="{{_name}}[auto_pop]" value="1" {{#if auto_pop}}checked="checked"{{/if}}><?php _e( 'Auto populate contact data with CiviCRM data if user is logged in. (Only for the first contact processor)', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="address_enabled" data-entity-accordion="civicrm-address-entity" type="checkbox" name="{{_name}}[enabled_entities][process_address]" value="1" {{#if enabled_entities/process_address}}checked="checked"{{/if}}><?php _e( 'Process address for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="phone_enabled" data-entity-accordion="civicrm-phone-entity" type="checkbox" name="{{_name}}[enabled_entities][process_phone]" value="1" {{#if enabled_entities/process_phone}}checked="checked"{{/if}}><?php _e( 'Process phone for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="note_enabled" data-entity-accordion="civicrm-note-entity" type="checkbox" name="{{_name}}[enabled_entities][process_note]" value="1" {{#if enabled_entities/process_note}}checked="checked"{{/if}}><?php _e( 'Process note for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="email_enabled" data-entity-accordion="civicrm-email-entity" type="checkbox" name="{{_name}}[enabled_entities][process_email]" value="1" {{#if enabled_entities/process_email}}checked="checked"{{/if}}><?php _e( 'Process email for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="website_enabled" data-entity-accordion="civicrm-website-entity" type="checkbox" name="{{_name}}[enabled_entities][process_website]" value="1" {{#if enabled_entities/process_website}}checked="checked"{{/if}}><?php _e( 'Process website for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="im_enabled" data-entity-accordion="civicrm-im-entity" type="checkbox" name="{{_name}}[enabled_entities][process_im]" value="1" {{#if enabled_entities/process_im}}checked="checked"{{/if}}><?php _e( 'Process Im for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="group_enabled" data-entity-accordion="civicrm-group-entity" type="checkbox" name="{{_name}}[enabled_entities][process_group]" value="1" {{#if enabled_entities/process_group}}checked="checked"{{/if}}><?php _e( 'Process group for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
	<div class="caldera-config-group caldera-config-group-full">
		<div class="caldera-config-field">
			<label><input id="tag_enabled" data-entity-accordion="civicrm-tag-entity" type="checkbox" name="{{_name}}[enabled_entities][process_tag]" value="1" {{#if enabled_entities/process_tag}}checked="checked"{{/if}}><?php _e( 'Process tag for this contact.', 'caldera-forms-civicrm' ); ?></label>
		</div>
	</div>
</div>
<hr style="clear: both;" />

<!-- === Contact entity === -->
<?php

// Get Contact Types
$contactTypeResult = civicrm_api3( 'ContactType', 'get', array(
	'sequential' => 0,
	'is_active' => 1,
	'parent_id' => array( 'IS NULL' => 1 ),
	'options' => array( 'limit' => 0 ),
));

$contactSubTypeResult = civicrm_api3( 'ContactType', 'get', array(
	'sequential' => 1,
	'parent_id' => array( 'IS NOT NULL' => 1 ),
	'is_active' => 1,
	'options' => array( 'limit' => 0 ),
));

$orgStandardFields = array( 'organization_name', 'sic_code', 'legal_name' );
$indStandardFields = array( 'first_name', 'last_name', 'middle_name', 'prefix_id', 'suffix_id', 'current_employer', 'birth_date', 'gender_id', 'job_title' );

?>

<!-- Contact Link -->
<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<!-- Contact Type -->
<h2><?php _e( 'Contact Type', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_type" class="caldera-config-group">
	<label><?php _e( 'Contact Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[civicrm_contact][contact_type]">
		<option value="" {{#is contact_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $contactTypeResult['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['name'] ); ?>" {{#is civicrm_contact/contact_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $value['label'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Contact Sub-Type -->
<div id="contact_sub_type" class="caldera-config-group">
	<label><?php _e( 'Contact Sub-Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[civicrm_contact][contact_sub_type]">
		<option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $contactSubTypeResult['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['name'] ); ?>" {{#is civicrm_contact/contact_sub_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $value['label'] . ' [' . $contactTypeResult['values'][$value['parent_id']]['label'] . ']' ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Dedupe Rules -->
<div id="dedupe_rule" class="caldera-config-group">
	<label><?php _e( 'Dedupe Rule', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[civicrm_contact][dedupe_rule]">
		<option value="" {{#is dedupe_rule value=""}}selected="selected"{{/is}}></option>
		<?php foreach( CiviCRM_Caldera_Forms_Helper::get_dedupe_rules() as $type => $rule ) {
			foreach ( $rule as $key => $value ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" data-crm-type="<?php echo esc_attr( $type ); ?>" {{#is civicrm_contact/dedupe_rule value=<?php echo $key; ?>}}selected="selected"{{/is}}><?php echo esc_html( "[{$type}] - {$value}" ); ?></option>
		<?php } } ?>
		</select>
	</div>
</div>
<hr/>

<!-- Contact Standard Fields -->
<a id="civicrm-standard-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Standard Fields', 'caldera-forms-civicrm' ); ?></a>
<div class="civicrm-standard-fields" style="display:none">
	<h2 style="display: inline-block;"><?php _e( 'Standard Fields', 'caldera-forms-civicrm' ); ?></h2>
	<?php

	$contactFieldsResult = civicrm_api3( 'Contact', 'getfields', array(
		'sequential' => 1,
	));

	$contactFields = array();
	foreach ( $contactFieldsResult['values'] as $key => $value ) {
		if ( in_array( $value['name'], caldera_forms_civicrm()->helper->contact_fields ) ) {
			$contactFields[$value['name']] = $value['title'];
		}
	}

	unset( $contactFields['id'], $contactFields['contact_type'], $contactFields['contact_sub_type'] );
	$contactFields = array_diff_key( $contactFields, caldera_forms_civicrm()->helper->get_contact_custom_fields() );

	foreach( $contactFields as $key => $value ) { ?>
	<div class="caldera-config-group" id="<?php echo esc_attr( $key ); ?>"
		<?php
			in_array( $key, $orgStandardFields ) ? $org = 'data-crm-type="Organization"' : $org = ''; echo $org;
			$key == 'household_name' ? $house = 'data-crm-type="Household"' : $house = ''; echo $house;
			in_array( $key, $indStandardFields ) ? $ind = 'data-crm-type="Individual"' : $ind = ''; echo $ind;
			?>>
		<label><?php echo __( $value, 'caldera-forms'); ?></label>
		<div class="caldera-config-field">
			<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_contact][<?php echo $key; ?>]" value="{{<?php echo 'civicrm_contact/' . $key; ?>}}">
		</div>
	</div>
	<?php } ?>
	<hr style="clear: both;" />
</div>

<!-- Contact Custom Fields -->
<a id="civicrm-custom-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Custom Fields', 'caldera-forms-civicrm' ); ?></a>
<div class="civicrm-custom-fields" style="display: none;">
	<h2 style="display: inline-block;"><?php _e( 'Custom Fields', 'caldera-forms-civicrm' ); ?></h2>
	<?php

	$contactCustomFields = caldera_forms_civicrm()->helper->get_contact_custom_fields();

	foreach( $contactCustomFields as $key => $value ) { ?>
		<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group" data-crm-type="<?php echo caldera_forms_civicrm()->helper->custom_field_extends( $key ); ?>">
			<label><?php echo esc_html( $value ); ?></label>
			<div class="caldera-config-field">
			  <input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_contact][<?php echo $key; ?>]" value="{{<?php echo 'civicrm_contact/' . $key; ?>}}">
			</div>
		</div>
	<?php } ?>
	<hr style="clear: both;" />
</div>

<!-- === Address entity === -->
<?php
	$address_fields = civicrm_api3( 'Address', 'getfields', [
		'sequential' => 1,
	] );

	$address_location_type = civicrm_api3( 'Address', 'getoptions', [
		'sequential' => 1,
		'field' => 'location_type_id',
	] );

	$fields = [ 'name', 'is_primary', 'is_billing', 'street_address', 'supplemental_address_1', 'supplemental_address_2', 'city', 'state_province_id', 'postal_code', 'country_id' ];
?>

<div class="civicrm-address-entity">
	<a id="civicrm-address-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Address', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-address-fields" style="display: none;">
		<h3><?php _e( 'Address Location Type', 'caldera-forms-civicrm' ); ?></h3>
		<div id="location_type_id" class="caldera-config-group">
			<label><?php echo __( 'Address Location Type', 'caldera-forms-civicrm' ); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_address][location_type_id]">
				<option value="" {{#is civicrm_address/location_type_id value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $address_location_type['values'] as $key => $value) { ?>
					<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is civicrm_address/location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
				<?php } ?>
				</select>
			</div>
		</div>

		<hr style="clear: both;" />

		<h2 style="display: inline-block;"><?php _e( 'Address Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php foreach( $address_fields['values'] as $key => $value ) {
			if ( in_array( $value['name'], $fields ) ) { ?>
			<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
				<label><?php echo esc_html( $value['title'] ); ?> </label>
				<div class="caldera-config-field">
				<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_address][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_address/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>

<!-- === Phone entity === -->
<?php
	$phone_fields = civicrm_api3( 'Phone', 'getfields', [
		'sequential' => 1,
	] );

	$phone_location_type = civicrm_api3( 'Phone', 'getoptions', [
		'sequential' => 1,
		'field' => 'location_type_id',
	] );

	$phone_type = civicrm_api3( 'Phone', 'getoptions', [
		'field' => 'phone_type_id',
	] );

	$pFields = array( 'is_primary', 'is_billing', 'phone', 'phone_numeric' );
?>

<div class="civicrm-phone-entity">
	<a id="civicrm-phone-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Phone', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-phone-fields" style="display: none;">

		<div id="location_type_id" class="caldera-config-group">
			<label><?php _e( 'Phone Location Type', 'caldera-forms-civicrm' ); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_phone][location_type_id]">
				<option value="" {{#is civicrm_phone/location_type_id value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $phone_location_type['values'] as $key => $value ) { ?>
					<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is civicrm_phone/location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
				<?php } ?>
				</select>
			</div>
		</div>

		<div id="phone_type_id" class="caldera-config-group">
			<label><?php _e( 'Phone Type', 'caldera-forms-civicrm' ); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_phone][phone_type_id]">
					<option value="" {{#is civicrm_phone/location_type_id value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $phone_type['values'] as $id => $type ) { ?>
					<option value="<?php echo esc_attr( $id ); ?>" {{#is civicrm_phone/phone_type_id value=<?php echo $id; ?>}}selected="selected"{{/is}}><?php echo esc_html( $type ); ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<hr style="clear: both;" />

		<h2 style="display: inline-block;"><?php _e( 'Phone Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php foreach( $phone_fields['values'] as $key => $value ) {
			if ( in_array( $value['name'], $pFields ) ) { ?>
			<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
				<label><?php echo esc_html( $value['title'] ); ?></label>
				<div class="caldera-config-field">
				<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_phone][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_phone/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>

<!-- === Note entity === -->
<?php
	$noteFields = civicrm_api3( 'Note', 'getfields', array(
		'sequential' => 1,
	));

	$nFields = array( 'note', 'subject' );
?>

<div class="civicrm-note-entity">
	<a id="civicrm-note-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Note', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-note-fields" style="display: none;">
		<h2 style="display: inline-block;"><?php _e( 'Note Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php foreach( $noteFields['values'] as $key => $value ) {
			if( in_array($value['name'], $nFields ) ){ ?>
			<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
				<label><?php echo esc_html( $value['title'] ); ?></label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_note][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_note/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>


<!-- === Email entity === -->
<?php
	$emailFields = civicrm_api3('Email', 'getfields', array(
		'sequential' => 1,
	));

	$emailLocationType = civicrm_api3('Email', 'getoptions', array(
		'sequential' => 1,
		'field' => "location_type_id",
	));

	$eFields = array( 'is_primary', 'is_billing', 'email', 'on_hold', 'is_bulkmail' );
?>
<div class="civicrm-email-entity">
	<a id="civicrm-email-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Email', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-email-fields" style="display: none;">
		<h2><?php _e( 'Email Location Type', 'caldera-forms-civicrm' ); ?></h2>
		<div id="location_type_id" class="caldera-config-group">
			<label><?php echo __('Email Location Type'); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_email][location_type_id]">
				<!-- <option value="" {{#is civicrm_email/location_type_id value=""}}selected="selected"{{/is}}></option> -->
				<?php foreach( $emailLocationType['values'] as $key => $value) { ?>
					<option value="<?php echo $value['key']; ?>" {{#is civicrm_email/location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<hr style="clear: both;" />

		<h2 style="display: inline-block;"><?php _e( 'Email Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php
			foreach( $emailFields['values'] as $key => $value ) {
				if( in_array($value['name'], $eFields ) ){ ?>
			<div id="<?php echo $value['name']; ?>" class="caldera-config-group">
				<label><?php echo __($value['title']); ?> </label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_email][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_email/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>

<!-- === Website entity === -->
<?php
	$websiteFields = civicrm_api3('Website', 'getfields', array(
		'sequential' => 1,
	));

	$websiteType = civicrm_api3('website', 'getoptions', array(
		'sequential' => 1,
		'field' => "website_type_id",
	));

	$wFields = array( 'url' );
?>

<div class="civicrm-website-entity">
	<a id="civicrm-website-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Website', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-website-fields" style="display: none;">
		<h2><?php _e( 'Website Type', 'caldera-forms-civicrm' ); ?></h2>
		<div id="website_type_id" class="caldera-config-group">
			<label><?php echo __('Website Type'); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_website][website_type_id]">
				<option value="" {{#is civicrm_website/website_type_id value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $websiteType['values'] as $key => $value) { ?>
					<option value="<?php echo $value['key']; ?>" {{#is civicrm_website/website_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<hr style="clear: both;" />

		<h2><?php _e( 'Website Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php
			foreach( $websiteFields['values'] as $key => $value ) {
				if( in_array($value['name'], $wFields ) ){ ?>
			<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
				<label><?php echo __($value['title']); ?> </label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_website][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_website/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>


<!-- === Im entity === -->
<?php
	$imFields = civicrm_api3('Im', 'getfields', array(
		'sequential' => 1,
	));

	$imType = civicrm_api3('Im', 'getoptions', array(
		'sequential' => 1,
		'field' => "location_type_id",
	));

	$iFields = array( 'name', 'provider_id', 'is_primary', 'is_billing' );
?>

<div class="civicrm-im-entity">
	<a id="civicrm-im-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Im', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-im-fields" style="display: none;">
		<h2><?php _e( 'Im Location Type', 'caldera-forms-civicrm' ); ?></h2>
		<div id="location_type_id" class="caldera-config-group">
			<label><?php echo __('Im Location Type'); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_im][location_type_id]">
				<option value="" {{#is civicrm_im/location_type_id value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $imType['values'] as $key => $value) { ?>
					<option value="<?php echo $value['key']; ?>" {{#is civicrm_im/location_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<hr style="clear: both;" />

		<h2><?php _e( 'Im Fields', 'caldera-forms-civicrm' ); ?></h2>
		<?php
			foreach( $imFields['values'] as $key => $value ) {
				if( in_array($value['name'], $iFields ) ){ ?>
			<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
				<label><?php echo __($value['title']); ?> </label>
				<div class="caldera-config-field">
					<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[civicrm_im][<?php echo $value['name']; ?>]" value="{{<?php echo 'civicrm_im/' . $value['name']; ?>}}">
				</div>
			</div>
		<?php } } ?>
		<hr style="clear: both;" />
	</div>
</div>


<!-- === Group entity === -->
<?php
	$groupsResult = civicrm_api3( 'Group', 'get', array(
		'sequential' => 1,
		'cache_date' => array('IS NULL' => 1),
		'is_active' => 1,
		'options' => array('limit' => 0),
	));
?>

<div class="civicrm-group-entity">
	<a id="civicrm-group-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Group', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-group-fields" style="display: none;">
		<h2><?php _e( 'Group Fields', 'caldera-forms-civicrm' ); ?></h2>
		<div class="caldera-config-group">
			<label><?php _e('Group', 'caldera-forms-civicrm' ); ?></label>
			<div class="caldera-config-field">
				<select class="block-input field-config" name="{{_name}}[civicrm_group][contact_group]">
				<option value="" {{#is civicrm_group/contact_group value=""}}selected="selected"{{/is}}></option>
				<?php foreach( $groupsResult['values'] as $key => $value ) { ?>
					<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is civicrm_group/contact_group value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['title'] ); ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<hr style="clear: both;" />
	</div>
</div>

<!-- === Tag entity === -->
<?php
	$tagResult = $result = civicrm_api3( 'Tag', 'get', array(
		'sequential' => 1,
		'used_for' => 'civicrm_contact',
		'options' => array( 'limit' => 0 ), 
	));
?>

<div class="civicrm-tag-entity">
	<a id="civicrm-tag-fields-button" class="button civicrm-accordion" style="width: 100%; margin-bottom: 5px;"><?php _e( 'Contact Tag', 'caldera-forms-civicrm' ); ?></a>
	<div class="civicrm-tag-fields" style="display: none;">
		<h2><?php _e( 'Tag(s)', 'caldera-forms-civicrm' ); ?></h2>
		<div class="caldera-config-group caldera-config-group-full">
			<div class="caldera-config-field">
				<?php foreach ( $tagResult['values'] as $key => $value ) { ?>
				<label><input id="entity_tag" type="checkbox" name="{{_name}}[civicrm_tag][entity_tag_<?php echo $value['id']; ?>]" value="<?php echo esc_attr( $value['id'] ); ?>" {{#if civicrm_tag/entity_tag_<?php echo $value['id']; ?>}}checked="checked"{{/if}}><?php echo esc_html( $value['name'] ); ?></label>
				<?php } ?>
			</div>
		</div>
		<hr style="clear: both;" />
	</div>
</div>

<script>
jQuery(document).ready( function($) {
	// Processor ID and container
	var prId = "{{_id}}";
	var prContainer = '#' + prId + '_settings_pane .caldera-config-processor-setup';

	// Show/hide accordions
	$( prContainer + ' .civicrm-entity-fields' ).click( function(){
		$( prContainer + ' .civicrm-entity-fields input' ).each( function(i, el){
			if( $( el ).attr('checked') == 'checked' ){
				$( prContainer + ' .' + $( this ).attr('data-entity-accordion') ).show();
			} else {
				$( prContainer + ' .' + $( this ).attr('data-entity-accordion') ).hide();
			}
		});
	}).trigger('click');

	// Toggle fields sections in accordion style
	$( prContainer + ' .civicrm-accordion' ).each( function( i, el ){
		$( this ).click( function(){
			$( prContainer + ' .' + $( this ).attr('id').replace( '-button', '' ) ).toggle('slow');
			$( this ).toggleClass('button-primary');
		})
	})

	// show/hide custom/standard fields based on contact type
	var cType = $('[name="config[processors][' + prId +'][config][civicrm_contact][contact_type]"]');
	var cTypeOption = $('[name="config[processors][' + prId +'][config][civicrm_contact][contact_type]"] option');
	var typeOptions = [];
	var cSubType = $('[name="config[processors][' + prId +'][config][civicrm_contact][contact_sub_type]"]');
	var cSubTypeOption = $('[name="config[processors][' + prId +'][config][civicrm_contact][contact_sub_type]"] option');
	var subTypeOptions = [];
	cTypeOption.each(function(){
		if($(this).val() != ''){
			typeOptions.push($(this).val());
		}
	});

	cSubTypeOption.each(function(){
		if($(this).val() != ''){
			subTypeOptions.push($(this).val());
		}
	});

	var cTypes = typeOptions.concat(subTypeOptions);
	cType.change( function(){
		$(prContainer + ' [data-crm-type]').each(function(i, el){
			if( $.inArray($(el).attr('data-crm-type'), cTypes) != -1 && $(el).attr('data-crm-type') != 'Contact' && $(el).attr('data-crm-type') == cType.val())
				$(el).show();
			if($.inArray($(el).attr('data-crm-type'), cTypes) != -1 && $(el).attr('data-crm-type') != '' && $(el).attr('data-crm-type') != cType.val())
				$(el).hide();
		})
	}).trigger('change');

	cSubType.change( function(){
		$(prContainer + ' .civicrm-custom-fields [data-crm-type]').filter(function(i, el){
			if($(el).attr('data-crm-type').indexOf(',') != -1){
				var types = $(el).attr('data-crm-type').split(',');
				if($.inArray( cSubType.val(), types ) != -1 || $.inArray( cType.val(), types ) != -1){
					$(el).show();
				} else {
					$(el).hide();
				}
			} else {
				if( $( el ).attr( 'data-crm-type' ) == cSubType.val() || $( el ).attr( 'data-crm-type' ) == cType.val()){
					$(el).show();
				} else if($( el ).attr( 'data-crm-type' ) != 'Contact'){
					$( el ).hide();
				}
			}
		})
	}).trigger('change');

	// Append Contact number
	$( prContainer + ' #contact_link select' ).change( function(){
		$('#processors-config-panel .' + prId + ' a span').text( $( prContainer + ' #contact_link select' ).val() );
	}).trigger( 'change' );
});
</script>
