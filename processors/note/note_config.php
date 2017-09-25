<?php

$noteFields = civicrm_api3( 'Note', 'getfields', array(
	'sequential' => 1,
));

$fields = array( 'note', 'subject' );

?>

<h2><?php _e( 'Contact Link', 'caldera-forms-civicrm' ); ?></h2>
<div id="contact_link" class="caldera-config-group">
	<label><?php _e( 'Link to', 'caldera-forms-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<?php CiviCRM_Caldera_Forms_Helper::contact_link_field() ?>
		<p><?php _e( 'Select which contact you want to link this processor to.', 'caldera-forms-civicrm' ); ?></p>
	</div>
</div>

<hr style="clear: both;" />

<h2 style="display: inline-block;"><?php _e( 'Note Fields', 'caldera-forms-civicrm' ); ?></h2>
<?php foreach( $noteFields['values'] as $key => $value ) {
	if( in_array($value['name'], $fields ) ){ ?>
	<div id="<?php echo esc_attr( $value['name'] ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value['title'] ); ?></label>
		<div class="caldera-config-field">
			<?php echo '{{{_field slug="' . $value['name'] . '"}}}'; ?>
		</div>
	</div>
<?php } } ?>

<div id="note_attachment" class="caldera-config-group">
	<label><?php _e( 'Attachment', 'caldera-forms-civicrm' ); ?></label>
	<div id="note_attachment" class="caldera-config-field">
		<?php echo '{{{_field type="file" slug="note_attachment"}}}'; ?>
	</div>
</div>

<hr style="clear: both;" />
