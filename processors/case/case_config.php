<?php

$caseTypes = civicrm_api3( 'Case', 'getoptions', array(
	'sequential' => 1,
	'field' => 'case_type_id',
));

$case_status = civicrm_api3( 'Case', 'getoptions', array(
	'sequential' => 1,
	'field' => 'case_status_id',
));

$caseFieldsResult = civicrm_api3( 'Case', 'getfields', array(
	'sequential' => 1,
	'api_action' => 'create'
));

$caseFields = array();
foreach ( $caseFieldsResult['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], array( 'case_type_id', 'id', 'is_deleted', 'status_id', 'activity_id', 'contact_id' ) ) ) {
		$caseFields[$value['name']] = $value['title'];
	}
}

?>

<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<label><input id="{{_id}}_dismiss_case" type="checkbox" name="{{_name}}[dismiss_case]" value="1" {{#if dismiss_case}}checked="checked"{{/if}}><?php _e( 'Do not create Case if the contact already has a Case of same type.', 'caldera-forms-civicrm' ); ?></label>
	</div>
</div>

<hr style="clear: both;" />

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field(); ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<!-- Case Type -->
<h2><?php _e( 'Case', 'caldera-forms-civicrm' ); ?></h2>
<div id="{{_id}}_case_type" class="caldera-config-group">
	<label><?php _e( 'Case Type', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[case_type_id]">
		<?php foreach ( $caseTypes['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is case_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<!-- Case status -->
<div id="{{_id}}_case_status_id" class="caldera-config-group">
	<label><?php _e( 'Case Status', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config required" name="{{_name}}[case_status_id]">
		<?php foreach ( $case_status['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['key'] ); ?>" {{#is case_status_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['value'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<hr style="clear: both;" />

<h2><?php _e( 'Case fields', 'caldera-forms-civicrm' ); ?></h2>
<?php
	foreach ( $caseFields as $key => $value ) {
		if( $key != 'creator_id' ){ ?>
		<div id="{{_id}}_<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
			<label><?php _e( $value, 'caldera-forms-civicrm' ); ?></label>
			<div class="caldera-config-field">
				<input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind <?php if( $key == 'subject') echo 'required'; ?>" name="{{_name}}[<?php echo $key; ?>]" value="{{<?php echo $key; ?>}}">
			</div>
		</div>
<?php } } ?>

<!-- Case Manager -->
<div class="caldera-config-group">
	<label><?php _e( 'Case Created By', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_creator_id" class="block-input field-config" style="width: 100%;" nonce="<?php echo wp_create_nonce('admin_get_civi_contact'); ?>" name="{{_name}}[creator_id]">
		</select>
	</div>
</div>

<script>
jQuery( document ).ready( function( $ ){
	cfc_select2_defaults( '#{{_id}}_creator_id', '{{creator_id}}' );
} );
</script>
