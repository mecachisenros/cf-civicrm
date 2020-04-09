<?php
$tokenFields = civicrm_api3( 'PaymentToken',
	'getfields',
	[
		'api_action' => 'get',
	] );
// contact id comes from the linked contact processor
$excludedFields = [ 'created_date', 'created_id', 'contact_id' ];

// payment processor type
$pp_result = civicrm_api3( 'PaymentProcessor',
	'get',
	[
		'sequential' => 1,
		'is_recur'   => 1,
	] );
?>

<h2><?php _e( 'Required Fields', 'cf-civicrm' ); ?></h2>
<div id="{{_id}}_contact_link" class="caldera-config-group">
    <label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
    <div class="caldera-config-field">
		<?php caldera_forms_civicrm()->helper->contact_link_field() ?>
        <p class="description"><?php _e( 'Select which contact you want to link this processor to.', 'cf-civicrm' ); ?></p>
    </div>
</div>

<div class="caldera-config-group">
    <label for="{{_id}}_is_delete">Delete token</label>
    <div class="caldera-config-field">
        <input id="{{_id}}_is_delete" type="checkbox" class="field-config" name="{{_name}}[is_delete]" value="1" {{#if is_delete}}checked="checked"{{/if}}>
        <p class="description"> If you want to delete a token, just set the token id. </p>
    </div>
</div>

<?php
foreach ( $tokenFields['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], $excludedFields ) && $value['required'] ) { ?>
        <div class="caldera-config-group">
            <label for="{{_id}}_<?php echo $value['name']; ?>"><?php echo __( $value['title'] ); ?></label>
            <div class="caldera-config-field">
			    <?php
			    switch ( $key ) {
				case 'payment_processor_id':
					?>
                    <select id="{{_id}}_<?php echo $value['name']; ?>" class="block-input field-config" name="{{_name}}[<?php echo $key ?>]" required>
						<?php foreach ( $pp_result['values'] as $pp_key => $pp_value ) { ?>
                            <option value="<?php echo esc_attr( $pp_value['id'] ); ?>"
                                    {{#is payment_processor_id value=<?php echo $pp_value['id']; ?>}}selected="selected"{{/is}}>
								<?php echo esc_html( $pp_value['name'] . ( $pp_value['is_test'] ? ' (TEST)' : '' ) ); ?>
                            </option>
						<?php } ?>
                    </select>
					<?php
					break;
				default:
					?>
                        <input id="{{_id}}_<?php echo $value['name']; ?>" type="text" id="{{_id}}_name"
                               class="block-input field-config magic-tag-enabled caldera-field-bind"
                               name="{{_name}}[<?php echo $key; ?>]"
                               value="{{<?php echo $key; ?>}}">
				<?php } ?>
                <p class="description"><?php print $value['description'] ?></p>
            </div>
        </div>
		<?php
	}
} ?>
<hr style="clear: both;"/>

<h2 style="display: inline-block;"><?php _e('Optional Fields', 'cf-civicrm'); ?></h2>
<?php
foreach ( $tokenFields['values'] as $key => $value ) {
	if ( ! in_array( $value['name'], $excludedFields ) && ! $value['required'] ) {
		switch ( $key ) {
			default:
				?>
                <div class="caldera-config-group">
                    <label for="{{_id}}_<?php echo $value['name']; ?>"><?php echo __( $value['title'] ); ?> </label>
                    <div class="caldera-config-field">
                        <input type="text" id="{{_id}}_<?php echo $value['name']; ?>"
                               class="block-input field-config magic-tag-enabled caldera-field-bind"
                               name="{{_name}}[<?php echo $key; ?>]"
                               value="{{<?php echo $key; ?>}}">
                        <p class="description" ><?php print $value['description'] ?></p>
                    </div>
                </div>
			<?php
		}
	}
} ?>
<hr style="clear: both;"/>
