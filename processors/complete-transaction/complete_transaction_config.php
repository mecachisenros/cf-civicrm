<?php
$contribution_recur_fields_result = civicrm_api3( 'ContributionRecur',
	'getfields',
	[
		'sequential' => 1,
		'api_action' => 'create',
	] );

$unit_result = civicrm_api3( 'OptionGroup',
	'get',
	[
		'sequential'          => 1,
		'name'                => "recur_frequency_units",
		'api.OptionValue.get' => [ 'option_group_id' => "\$value.id" ],
	] );
$unit_result = array_shift( $unit_result['values'] )['api.OptionValue.get'];

$pp_result = civicrm_api3( 'PaymentProcessor',
	'get',
	[
		'sequential' => 1,
		'is_recur'   => 1,
	] );

$contribution_recur_fields = [];
foreach ( $contribution_recur_fields_result['values'] as $key => $value ) {
	if ( in_array( $value['name'], caldera_forms_civicrm()->helper->contribution_recur_fields ) ) {
		$contribution_recur_fields[ $value['name'] ] = $value['title'];
	}
}
?>

    <h2><?php _e( 'Contribution', 'cf-civicrm' ); ?></h2>
    <div id="{{_id}}contribution_id" class="caldera-config-group">
        <label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
        <div class="caldera-config-field">
	        <?php echo '{{{_field required="true" slug="contribution_id"}}}' ?>
        </div>
        <p class="description"><?php _e( 'The contribution to be validated.', 'cf-civicrm' ); ?></p>
    </div>
    <div id="{{_id}}transaction_id" class="caldera-config-group">
        <label><?php _e( 'Transaction ID', 'cf-civicrm' ); ?></label>
        <div class="caldera-config-field">
			<?php echo '{{{_field slug="trxn_id"}}}' ?>
        </div>
    </div>
    <div id="{{_id}}contribution_status" class="caldera-config-group">
        <label><?php _e('Contribution Status', 'cf-civicrm'); ?></label>
        <div class="caldera-config-field">
            {{{_field required="true" slug="contribution_status"}}}
        </div>
        <div class="caldera-config-field">
            <label><input type="checkbox" name="{{_name}}[create_recur]" value="1" {{#if create_recur}}checked="checked"{{/if}}><?php _e( 'Create a recurring contribution if completed.', 'cf-civicrm' ); ?></label>
        </div>
    </div>

    <h2><?php _e( 'Recurring Contribution Fields', 'cf-civicrm' ); ?></h2>
<?php
foreach ( $contribution_recur_fields as $key => $value ) { ?>
    <div id="{{_id}}_<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
        <label><?php echo esc_html( $value ); ?></label>
        <div class="caldera-config-field">
			<?php
			if ( $key == 'frequency_unit' ) {
				?>
                <select class="block-input field-config" name="{{_name}}[<?php echo $key ?>]" required>
					<?php foreach ( $unit_result['values'] as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $value['value'] ); ?>" {{#is frequency_unit
                                value="<?php echo $value['value']; ?>" }}selected="selected"
                                {{/is}}><?php echo esc_html( $value['label'] ); ?></option>
					<?php } ?>
                </select>
				<?php
			} elseif ( $key == 'payment_processor_id' ) {
				?>
                <select class="block-input field-config" name="{{_name}}[<?php echo $key ?>]" required>
					<?php foreach ( $pp_result['values'] as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is payment_processor_id
                                value="<?php echo $value['id']; ?>" }}selected="selected"
                                {{/is}}><?php echo esc_html( $value['title'] . ( $value['is_test'] ? ' (TEST)'
								: '' ) ); ?></option>
					<?php } ?>
                </select>
				<?php
			} else {
				echo '{{{_field ';
				if ( in_array( $key, [ 'frequency_interval', 'payment_processor_id' ] ) ) {
					echo 'required="true" ';
				}
				echo 'slug="' . $key . '"}}}';
			}
			?>
        </div>
    </div>
<?php } ?>