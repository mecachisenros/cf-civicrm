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
	<p class="description">
		This processor is designed for working with the CiviCRM Order Processor.
		The Order processor should create an order in pending status, and completed/failed in this processor.
		Between the Order processor and this processor, you can add a payment processor to make a transaction,
		and update the order based on the result.
		<br/>
		Currently, the only supported payment processor is eWay Rapid.
	</p>
	<h2><?php _e( 'Contribution', 'cf-civicrm' ); ?></h2>
	<div id="{{_id}}contribution_id" class="caldera-config-group">
		<label><?php _e( 'Link to', 'cf-civicrm' ); ?></label>
		<div class="caldera-config-field">
			<?php echo '{{{_field required="true" slug="contribution_id"}}}' ?>
		</div>
		<p class="description"><?php _e( 'The contribution id to be updated.', 'cf-civicrm' ); ?></p>
	</div>

	<h2><?php _e( 'Recurring Contribution Fields', 'cf-civicrm' ); ?></h2>
<?php
foreach ( $contribution_recur_fields as $key => $value ) { ?>
	<div id="{{_id}}_<?php echo esc_attr( $key ); ?>" class="caldera-config-group">
		<label><?php echo esc_html( $value ); ?></label>
		<div class="caldera-config-field">
			<?php if ( $key == 'payment_processor_id' ) {
				?>
				<select class="block-input field-config" name="{{_name}}[<?php echo $key ?>]" required>
					<?php foreach ( $pp_result['values'] as $key => $value ) { ?>
						<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is payment_processor_id value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value['name'] . ( $value['is_test'] ? ' (TEST)' : '' ) ); ?></option>
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
		<?php if ($key == 'frequency_unit'): ?>
			<p class="description">Use one of the unit: day, week, month or year.</p>
		<?php elseif ($key == 'payment_processor_id'): ?>
			<p class="description">This is the CiviCRM payment processor to be used in the recurring contribution.</p>
		<?php endif; ?>
	</div>
<?php } ?>