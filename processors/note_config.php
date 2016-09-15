<?php
    $noteFields = civicrm_api3('Note', 'getfields', array(
		'sequential' => 1,
	));

    $fields = array( 'note', 'subject' );
?>

<h2>Contact Link</h2>
<div id="contact_link" class="caldera-config-group">
    <label><?php echo __('Link to'); ?></label>
    <div class="caldera-config-field">
        <?php CiviCRM_Caldera_Forms_Helper::contact_link_field(); ?>
        <p>Select which contact you want to link this processor to.</p>
    </div>
</div>
<hr style="clear: both;" />

<h2 style="display: inline-block;">Note Fields</h2>
<?php
    foreach( $noteFields['values'] as $key => $value ) {
        if( in_array($value['name'], $fields ) ){ ?>
    <div id="<?php echo $value['name']; ?>" class="caldera-config-group">
        <label><?php echo __($value['title']); ?> </label>
        <div class="caldera-config-field">
          <?php echo __('{{{_field slug="' . $value['name'] . '"}}}'); ?>
        </div>
    </div>
<?php } } ?>
<hr style="clear: both;" />
