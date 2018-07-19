<?php

$entities = [
	'civicrm_participant' => __( 'CiviCRM Participant', 'caldera-forms-civicrm' ),
	'civicrm_membership' => __( 'CiviCRM Membership', 'caldera-forms-civicrm' ),
	'civicrm_contribution' => __( 'CiviCRM Contribution', 'caldera-forms-civicrm' ),
];

$membership_types = civicrm_api3( 'MembershipType', 'get', [
	'sequential' => 1,
	'is_active' => 1,
	'visibility' => 'Public',
	'options' => [ 'limit' => 0 ],
] );

$fields = civicrm_api3( 'LineItem', 'getfields', [
	'api_action' => 'create',
] );

$price_sets = caldera_forms_civicrm()->helper->get_price_sets();
$price_sets_json = json_encode( $price_sets );

?>

<pre>
<?php //print_r( json_encode( $price_sets ) ); ?>
</pre>

<div id="{{_id}}_entity_table" class="caldera-config-group entity">
    <label><?php _e( 'Entity Table', 'caldera-forms-civicrm' );?></label>
    <select class="caldera-config-field required" name="{{_name}}[entity_table]">
        <option value="" {{#is entity_table value=""}}selected="selected"{{/is}}></option>
        <?php foreach ( $entities as $entity => $entity_name ) { ?>
            <option value="<?php echo esc_attr( $entity ); ?>" {{#is entity_table value="<?php echo $entity; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $entity_name ); ?></option>
        <?php } ?>
    </select>
</div>

<div id="{{_id}}_entity_data" class="entity-data caldera-config-group">
    <label><?php _e( 'Entity Data (Membership or Participant)', 'caldera-forms-civicrm' ); ?></label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[entity_params]" value="{{entity_params}}">
    </div>
</div>

<div id="{{_id}}_price_field_value" class="caldera-config-group">
    <label><?php _e('Price Field Value', 'caldera-forms-civicrm');?></label>
    <div class="binded_price_field caldera-config-field">
        <input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind" id="{{_id}}" name="{{_name}}[price_field_value]" value="{{price_field_value}}">
    </div>
    <div class="is_fixed caldera-config-field">
        <label><input type="checkbox" name="{{_name}}[is_fixed_price_field]" value="1" {{#if is_fixed_price_field}}checked="checked"{{/if}}><?php _e( 'Use a fixed Price Field', 'caldera-forms-civicrm' ); ?></label>
    </div>
    <div class="fixed_price_field caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[fixed_price_field_value]">
            <option value=""><?php _e( 'Select a Price Field', 'caldera-forms-civicrm' ); ?></option>
            <?php
                foreach ( $price_sets as $price_set_id => $price_set ) {
                	echo '<optgroup label="' . $price_set['title'] . '">';
                	foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
                		echo '<optgroup label="' . $price_field['label'] . '">';
                		foreach ( $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) {
                			echo '<option value="' . esc_attr( $price_field_value_id ) . '" {{#is fixed_price_field_value value=' . $price_field_value_id . '}}selected="selected"{{/is}}>' . esc_html( $price_field_value['label'] ) . '</option>';
                		}
                		echo '</optgroup>';
                	}
                	echo '</optgroup>';
                }
                ?>
        </select>
    </div>
</div>

<!-- <div class="second_line_item caldera-config-field">
    <label><input type="checkbox" name="{{_name}}[second_price_field]" value="1" {{#if second_price_field}}checked="checked"{{/if}}><?php _e('Add a second Line Item based on price field', 'caldera-forms-civicrm');?></label>
</div>

<div id="{{_id}}_price_field_value_2" class="caldera-config-group">
    <label><?php _e( 'Second Price Field Value', 'caldera-forms-civicrm' ); ?></label>
    <div class="fixed_price_field caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[fixed_price_field_value_2]">
            <option value=""><?php _e( 'Select a Price Field', 'caldera-forms-civicrm' ); ?></option>
            <?php
                foreach ( $price_sets as $price_set_id => $price_set ) {
                    echo '<optgroup label="' . $price_set['title'] . '">';
                    foreach ( $price_set['price_fields'] as $price_field_id => $price_field ) {
                        echo '<optgroup label="' . $price_field['label'] . '">';
                        foreach ( $price_field['price_field_values'] as $price_field_value_id => $price_field_value ) {
                            echo '<option value="' . esc_attr( $price_field_value_id ) . '" {{#is fixed_price_field_value_2 value=' . $price_field_value_id . '}}selected="selected"{{/is}}>' . esc_html( $price_field_value['label'] ) . '</option>';
                        }
                        echo '</optgroup>';
                    }
                    echo '</optgroup>';
                }
                ?>
        </select>
    </div>
</div> -->

<script>
    (function(){
        var prId = '{{_id}}',
        price_field_value = '#' + prId + '_price_field_value',
        entity_table = '#' + prId + '_entity_table';



        $( price_field_value + ' .is_fixed input' ).on( 'change', function( i, el ) {
            var is_fixed = $( this ).prop( 'checked' );
            $( '.binded_price_field', $( price_field_value ) ).toggle( ! is_fixed );
            $( '.fixed_price_field', $( price_field_value ) ).toggle( is_fixed );
        } ).trigger( 'change' );

    })();
</script>