<?php

$relationships = civicrm_api3('RelationshipType', 'get', array(
  'sequential' => 1,
));

?>

<div id="relationship_type" class="caldera-config-group">
    <label><?php echo __('Relationship Type'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[relationship_type]">
        <?php foreach( $relationships['values'] as $key => $value) { ?>
            <option value="<?php echo $value['id']; ?>" {{#is relationship_type value=<?php echo $value['id']; ?>}}selected="selected"{{/is}}><?php echo '[' . $value['contact_type_a'] . ']' . $value['label_a_b'] . ' - ['. $value['contact_type_b'] . ']' . $value['label_b_a']; ?></option>
        <?php } ?>
        </select>
    </div>
</div>

<div id="contact_a" class="caldera-config-group">
    <label><?php echo __('Contact A'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[contact_a]">
            <option value="1" {{#is contact_a value=1}}selected="selected"{{/is}}>Contact 1</option>
            <option value="2" {{#is contact_a value=2}}selected="selected"{{/is}}>Contact 2</option>
            <option value="3" {{#is contact_a value=3}}selected="selected"{{/is}}>Contact 3</option>
            <option value="4" {{#is contact_a value=4}}selected="selected"{{/is}}>Contact 4</option>
            <option value="4" {{#is contact_a value=4}}selected="selected"{{/is}}>Contact 4</option>
            <option value="5" {{#is contact_a value=5}}selected="selected"{{/is}}>Contact 5</option>
            <option value="6" {{#is contact_a value=6}}selected="selected"{{/is}}>Contact 6</option>
            <option value="7" {{#is contact_a value=7}}selected="selected"{{/is}}>Contact 7</option>
            <option value="8" {{#is contact_a value=8}}selected="selected"{{/is}}>Contact 8</option>
            <option value="9" {{#is contact_a value=9}}selected="selected"{{/is}}>Contact 9</option>
            <option value="10" {{#is contact_a value=10}}selected="selected"{{/is}}>Contact 10</option>
        </select>
    </div>
</div>

<div id="contact_b" class="caldera-config-group">
    <label><?php echo __('Contact B'); ?></label>
    <div class="caldera-config-field">
        <select class="block-input field-config" name="{{_name}}[contact_b]">
            <option value="1" {{#is contact_b value=1}}selected="selected"{{/is}}>Contact 1</option>
            <option value="2" {{#is contact_b value=2}}selected="selected"{{/is}}>Contact 2</option>
            <option value="3" {{#is contact_b value=3}}selected="selected"{{/is}}>Contact 3</option>
            <option value="4" {{#is contact_b value=4}}selected="selected"{{/is}}>Contact 4</option>
            <option value="4" {{#is contact_b value=4}}selected="selected"{{/is}}>Contact 4</option>
            <option value="5" {{#is contact_b value=5}}selected="selected"{{/is}}>Contact 5</option>
            <option value="6" {{#is contact_b value=6}}selected="selected"{{/is}}>Contact 6</option>
            <option value="7" {{#is contact_b value=7}}selected="selected"{{/is}}>Contact 7</option>
            <option value="8" {{#is contact_b value=8}}selected="selected"{{/is}}>Contact 8</option>
            <option value="9" {{#is contact_b value=9}}selected="selected"{{/is}}>Contact 9</option>
            <option value="10" {{#is contact_b value=10}}selected="selected"{{/is}}>Contact 10</option>
        </select>
    </div>
</div>
