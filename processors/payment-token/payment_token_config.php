<?php
$tokenFields = civicrm_api3( 'PaymentToken',
	'getfields',
	[
		'api_action' => 'get',
	] );
// contact id comes from the linked contact processor
$excludedFields = [ 'created_date', 'created_id', 'contact_id' ];

?>

<h2><?php _e( 'Required Fields', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
    <label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
    <div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
        <p><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
    </div>
</div>

<?php
foreach ( $tokenFields['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], $excludedFields ) && $value['required'] ) { ?>
        <div id="{{_id}}_<?php echo $value['name']; ?>" class="caldera-config-group">
            <label><?php echo __( $value['title'] ); ?> </label>
            <div class="caldera-config-field">
				<?php echo __( '{{{_field slug="' . $value['name'] . '"}}}' ); ?>
            </div>
        </div>
	<?php }
} ?>
<hr style="clear: both;"/>

<h2 style="display: inline-block;">Optional Fields</h2>
<?php
foreach ( $tokenFields['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], $excludedFields ) && ! $value['required'] ) { ?>
        <div id="{{_id}}_<?php echo $value['name']; ?>" class="caldera-config-group">
            <label><?php echo __( $value['title'] ); ?> </label>
            <div class="caldera-config-field">
				<?php echo __( '{{{_field slug="' . $value['name'] . '"}}}' ); ?>
            </div>
        </div>
	<?php }
} ?>
<hr style="clear: both;"/>
