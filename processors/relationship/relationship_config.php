<?php

$relationships = civicrm_api3( 'RelationshipType', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

$relationshipFields = civicrm_api3( 'Relationship', 'getfields', [] );

$fields = [ 'start_date', 'end_date' ];

?>

<div id="{{_id}}_relationship_type" class="caldera-config-group">
	<label><?php echo __( 'Relationship Type', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[relationship_type]">
		<?php foreach( $relationships['values'] as $key => $value ) { ?>
			<option value="<?php echo esc_attr( $value['id'] ); ?>" {{#is relationship_type value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo esc_html( '[' . $value['contact_type_a'] . ']' . $value['label_a_b'] . ' - ['. $value['contact_type_b'] . ']' . $value['label_b_a'] ); ?></option>
		<?php } ?>
		</select>
	</div>
</div>

<div id="{{_id}}_contact_a" class="caldera-config-group">
	<label><?php _e( 'Contact A', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_a]">
			<option value="1" {{#is contact_a value=1}}selected="selected"{{/is}}><?php _e( 'Contact 1', 'cf-civicrm' ); ?></option>
			<option value="2" {{#is contact_a value=2}}selected="selected"{{/is}}><?php _e( 'Contact 2', 'cf-civicrm' ); ?></option>
			<option value="3" {{#is contact_a value=3}}selected="selected"{{/is}}><?php _e( 'Contact 3', 'cf-civicrm' ); ?></option>
			<option value="4" {{#is contact_a value=4}}selected="selected"{{/is}}><?php _e( 'Contact 4', 'cf-civicrm' ); ?></option>
			<option value="5" {{#is contact_a value=5}}selected="selected"{{/is}}><?php _e( 'Contact 5', 'cf-civicrm' ); ?></option>
			<option value="6" {{#is contact_a value=6}}selected="selected"{{/is}}><?php _e( 'Contact 6', 'cf-civicrm' ); ?></option>
			<option value="7" {{#is contact_a value=7}}selected="selected"{{/is}}><?php _e( 'Contact 7', 'cf-civicrm' ); ?></option>
			<option value="8" {{#is contact_a value=8}}selected="selected"{{/is}}><?php _e( 'Contact 8', 'cf-civicrm' ); ?></option>
			<option value="9" {{#is contact_a value=9}}selected="selected"{{/is}}><?php _e( 'Contact 9', 'cf-civicrm' ); ?></option>
			<option value="10" {{#is contact_a value=10}}selected="selected"{{/is}}><?php _e( 'Contact 10', 'cf-civicrm' ); ?></option>
		</select>
	</div>
</div>

<div id="{{_id}}_contact_b" class="caldera-config-group">
	<label><?php _e( 'Contact B', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_b]">
			<option value="1" {{#is contact_b value=1}}selected="selected"{{/is}}><?php _e( 'Contact 1', 'cf-civicrm' ); ?></option>
			<option value="2" {{#is contact_b value=2}}selected="selected"{{/is}}><?php _e( 'Contact 2', 'cf-civicrm' ); ?></option>
			<option value="3" {{#is contact_b value=3}}selected="selected"{{/is}}><?php _e( 'Contact 3', 'cf-civicrm' ); ?></option>
			<option value="4" {{#is contact_b value=4}}selected="selected"{{/is}}><?php _e( 'Contact 4', 'cf-civicrm' ); ?></option>
			<option value="5" {{#is contact_b value=5}}selected="selected"{{/is}}><?php _e( 'Contact 5', 'cf-civicrm' ); ?></option>
			<option value="6" {{#is contact_b value=6}}selected="selected"{{/is}}><?php _e( 'Contact 6', 'cf-civicrm' ); ?></option>
			<option value="7" {{#is contact_b value=7}}selected="selected"{{/is}}><?php _e( 'Contact 7', 'cf-civicrm' ); ?></option>
			<option value="8" {{#is contact_b value=8}}selected="selected"{{/is}}><?php _e( 'Contact 8', 'cf-civicrm' ); ?></option>
			<option value="9" {{#is contact_b value=9}}selected="selected"{{/is}}><?php _e( 'Contact 9', 'cf-civicrm' ); ?></option>
			<option value="10" {{#is contact_b value=10}}selected="selected"{{/is}}><?php _e( 'Contact 10', 'cf-civicrm' ); ?></option>
		</select>
	</div>
</div>

<?php
foreach ( $relationshipFields['values'] as $value ) {
	if ( in_array($value['name'], $fields ) ) {
		?>
		<div id="{{_id}}_<?= $value['name'] ?>" class="caldera-config-group">
			<label><?= __($value['title'], 'cf-civicrm') ?></label>
			<div class="caldera-config-field">
				{{{_field slug="<?= $value['name'] ?>"}}}
			</div>
		</div>
		<?php
	}
}
