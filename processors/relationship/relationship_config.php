<?php

$relationships = civicrm_api3( 'RelationshipType', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

$relationshipPermissions = array(
  '0' => 'None',
  '2' => 'View only',
  '1' => 'View and update',
);

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
			<?php
			$maxcontacts = caldera_forms_civicrm()->maxcontacts;
			for ($count = 1; $count <= $maxcontacts; $count++) {
				echo '<option value="' . $count . '" {{#is contact_a value='. $count . '}}selected="selected"{{/is}}>' . sprintf(__('Contact %d', 'cf-civicrm'), $count) . '</option>';
			}
			?>
		</select>
	</div>
</div>

<div id="{{_id}}_contact_b" class="caldera-config-group">
	<label><?php _e( 'Contact B', 'cf-civicrm' ); ?></label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_b]">
			<?php
			$maxcontacts = caldera_forms_civicrm()->maxcontacts;
			for ($count = 1; $count <= $maxcontacts; $count++) {
				echo '<option value="' . $count . '" {{#is contact_b value='. $count . '}}selected="selected"{{/is}}>' . sprintf(__('Contact %d', 'cf-civicrm'), $count) . '</option>';
			}
			?>
		</select>
	</div>
</div>

<div id="{{_id}}_permission_a_b" class="caldera-config-group">
    <label><?php _e( 'Contact A has Permission Over Contact B', 'caldera-forms-civicrm' ); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[permission_a_b]">
          <?php foreach( $relationshipPermissions as $key => $value ) { ?>
              <option value="<?php echo esc_attr( $key ); ?>" {{#is permission_a_b value=<?php echo $key; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value ); ?></option>
          <?php } ?>
        </select>
    </div>
</div>

<div id="{{_id}}_permission_b_a" class="caldera-config-group">
    <label><?php _e( 'Contact A has Permission Over Contact B', 'caldera-forms-civicrm' ); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[permission_b_a]">
          <?php foreach( $relationshipPermissions as $key => $value ) { ?>
              <option value="<?php echo esc_attr( $key ); ?>" {{#is permission_b_a value=<?php echo $key; ?>}}selected="selected"{{/is}}><?php echo esc_html( $value ); ?></option>
          <?php } ?>
        </select>
    </div>
</div>