<?php

$tagResult = $result = civicrm_api3( 'Tag', 'get', array(
	'sequential' => 1,
	'used_for' => 'civicrm_contact',
	'options' => array( 'limit' => 0 ),
));

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

<h2><?php _e( 'Tag(s)', 'caldera-forms-civicrm' ); ?></h2>
<div class="caldera-config-group caldera-config-group-full">
	<div class="caldera-config-field">
		<?php foreach ( $tagResult['values'] as $key => $value ) { ?>
		<label><input id="entity_tag" type="checkbox" name="{{_name}}[entity_tag_<?php echo $value['id']; ?>]" value="<?php echo esc_attr( $value['id'] ); ?>" {{#if entity_tag_<?php echo $value['id']; ?>}}checked="checked"{{/if}}><?php echo esc_html( $value['name'] ); ?></label>
		<?php } ?>
	</div>
</div>
