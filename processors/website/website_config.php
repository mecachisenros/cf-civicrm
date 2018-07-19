<?php
	$websiteFields = civicrm_api3('Website', 'getfields', array(
		'sequential' => 1,
	));

	$websiteType = civicrm_api3('website', 'getoptions', array(
		'sequential' => 1,
		'field' => "website_type_id",
	));

	$fields = array( 'url' );
?>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>
<hr style="clear: both;" />

<h2><?php _e( 'Website Type', 'caldera-forms-civicrm' ); ?></h2>
<div id="website_type_id" class="caldera-config-group">
	<label><?php echo __('Website Type'); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[website_type_id]">
		<?php foreach( $websiteType['values'] as $key => $value) { ?>
			<option value="<?php echo $value['key']; ?>" {{#is website_type_id value=<?php echo $value['key']; ?>}}selected="selected"{{/is}}><?php echo $value['value']; ?></option>
		<?php } ?>
		</select>
	</div>
</div>
<hr style="clear: both;" />

<h2><?php _e( 'Website Fields', 'caldera-forms-civicrm' ); ?></h2>
<?php
	foreach( $websiteFields['values'] as $key => $value ) {
		if( in_array($value['name'], $fields ) ){ ?>
	<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
		<label><?php echo __($value['title']); ?> </label>
		<div class="caldera-config-field">
		  <?php echo __('{{{_field slug="' . $value['name'] . '"}}}'); ?>
		</div>
	</div>
<?php } } ?>
<hr style="clear: both;" />
