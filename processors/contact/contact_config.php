<?php

// Get Contact Types
$contactTypeResult = civicrm_api3( 'ContactType', 'get', array(
	'sequential' => 0,
	'is_active' => 1,
	'parent_id' => array( 'IS NULL' => 1 ),
));

$contactSubTypeResult = civicrm_api3( 'ContactType', 'get', array(
	'sequential' => 1,
	'parent_id' => array( 'IS NOT NULL' => 1 ),
));

$orgStandardFields = array( 'organization_name', 'sic_code', 'legal_name' );
$indStandardFields = array( 'first_name', 'last_name', 'middle_name', 'prefix_id', 'suffix_id', 'current_employer', 'birth_date', 'gender_id', 'job_title' );

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="auto_pop" type="checkbox" name="{{_name}}[auto_pop]" value="1" {{#if auto_pop}}checked="checked"{{/if}}><?php _e( 'Auto populate contact data with CiviCRM data if user is logged in. (Only for the first contact processor)', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<hr style="clear: both;" />

<!-- Contact Link -->
<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<!-- Contact Type -->
<h2><?php _e( 'Contact Type', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_type" class="caldera-config-group">
	<label><?php _e( 'Contact Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[contact_type]">
		<option value="" {{#is contact_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $contactTypeResult['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['name'] ); ?>" {{#is contact_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $value['label'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Contact Sub-Type -->
<div id="contact_sub_type" class="caldera-config-group">
	<label><?php _e( 'Contact Sub-Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_sub_type]">
		<option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach( $contactSubTypeResult['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['name'] ); ?>" {{#is contact_sub_type value="<?php echo $value['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $value['label'] . ' [' . $contactTypeResult['values'][$value['parent_id']]['label'] . ']' ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Dedupe Rules -->
<div id="dedupe_rule" class="caldera-config-group">
	<label><?php _e( 'Dedupe Rule', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[dedupe_rule]">
		<option value="" {{#is dedupe_rule value=""}}selected="selected"{{/is}}></option>
		<?php foreach( CiviCRM_Caldera_Forms_Helper::get_dedupe_rules() as $type => $rule ) {
			foreach ( $rule as $key => $value ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" data-crm-type="<?php echo esc_attr( $type ); ?>" {{#is dedupe_rule value=<?php echo $key; ?>}}selected="selected"{{/is}}><?php echo esc_html( "[{$type}] - {$value}" ); ?></option>
		<?php } } ?>
		</select>
	</div>
</div>
<hr/>

<!-- Contact Fields -->
<div class="civicrm-standard-fields">
<h2 style="display: inline-block;"><?php _e( 'Standard Fields', 'caldera-forms-civicrm' ); ?></h2>
<!-- <button id="clear_fields" style="float: right;" type="button">Clear fields</button> -->
<?php

$contactFieldsResult = civicrm_api3( 'Contact', 'getfields', array(
	'sequential' => 1,
));

$contactFields = array();
foreach ( $contactFieldsResult['values'] as $key => $value ) {
	if ( in_array( $value['name'], CiviCRM_Caldera_Forms_Helper::$contact_fields ) ) {
		$contactFields[$value['name']] = $value['title'];
	}
}

unset( $contactFields['id'], $contactFields['contact_type'], $contactFields['contact_sub_type'] );
$contactFields = array_diff_key( $contactFields, CiviCRM_Caldera_Forms_Helper::get_contact_custom_fields() );

foreach( $contactFields as $key => $value ) { ?>
	<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group"
		<?php
		in_array( $key, $orgStandardFields ) ? $org = 'data-crm-type="Organization"' : $org = ''; echo $org;
		$key == 'household_name' ? $house = 'data-crm-type="Household"' : $house = ''; echo $house;
		in_array( $key, $indStandardFields ) ? $ind = 'data-crm-type="Individual"' : $ind = ''; echo $ind;
		?>>
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
		  <?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } ?>

</div>

<hr style="clear: both;" />

<!-- Contact Custom Fields -->
<div class="civicrm-custom-fields">
<h2 style="display: inline-block;"><?php _e( 'Custom Fields', 'caldera-forms-civicrm' ); ?></h2>
<!-- <button id="clear_custom_fields" style="float: right;" type="button">Clear fields</button> -->
<?php

$contactCustomFields = CiviCRM_Caldera_Forms_Helper::get_contact_custom_fields();

foreach( $contactCustomFields as $key => $value ) { ?>
	<div id="<?php echo esc_attr( $key ); ?>" class="caldera-config-group" data-crm-type="<?php echo CiviCRM_Caldera_Forms_Helper::custom_field_extends( $key ); ?>">
		<label><?php echo esc_html( $value ); ?> </label>
		<div class="caldera-config-field">
		  <?php echo '{{{_field slug="' . $key . '"}}}'; ?>
		</div>
	</div>
<?php } ?>
</div>

<script>
jQuery(document).ready( function() {
	var $ = jQuery;
	var prName = "{{_name}}";
	var prId = prName.match(/fp_([0-9]){8}/g);
	var prContainer = '#' + prId + '_settings_pane .caldera-config-processor-setup';
	var cType = $('[name="config[processors][' + prId +'][config][contact_type]"]');
	var cTypeOption = $('[name="config[processors][' + prId +'][config][contact_type]"] option');
	var typeOptions = [];
	var cSubType = $('[name="config[processors][' + prId +'][config][contact_sub_type]"]');
	var cSubTypeOption = $('[name="config[processors][' + prId +'][config][contact_sub_type]"] option');
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
				} else if($( el ).attr( 'data-crm-type' ) != 'Contact')
					$( el ).hide();
				}
			}
		})
	}).trigger('change');

	$( prContainer + ' #contact_link select' ).change( function(){
		$('#processors-config-panel .' + prId + ' a span').text( $( prContainer + ' #contact_link select' ).val() );
	}).trigger( 'change' );
});
</script>
