<?php 
$contact_types = civicrm_api3( 'ContactType', 'get', [
	'sequential' => 0,
	'is_active' => 1,
	'parent_id' => [ 'IS NULL' => 1 ],
	'options' => [ 'limit' => 0 ],
] );

$contact_sub_types = civicrm_api3( 'ContactType', 'get', [
	'sequential' => 1,
	'parent_id' => [ 'IS NOT NULL' => 1 ],
	'is_active' => 1,
	'options' => [ 'limit' => 0 ],
] );

?>

<!-- Placeholder -->
<div class="caldera-config-group">
	<label for="{{_id}}_placeholder">
		<?php esc_html_e( 'Placeholder', 'caldera-forms' ); ?>
	</label>
	<div class="caldera-config-field">
		<input type="text" id="{{_id}}_placeholder" class="block-input field-config" name="{{_name}}[placeholder]" value="{{placeholder}}">
	</div>
</div>

<!-- Contact Type -->
<div class="caldera-config-group">
	<label for="{{_id}}_contact_type">
		<?php esc_html_e( 'Contact Type', 'caldera-forms-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_type]">
		<option value="" {{#is contact_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach ( $contact_types['values'] as $key => $type ) { ?>
			<option value="<?php echo esc_attr( $type['name'] ); ?>" {{#is contact_type value="<?php echo $type['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $type['label'] ); ?></option>
		<?php } ?>
		</select>
		<p class="description" id="{{_id}}_contact_type">
			<?php esc_html_e( 'Limit by Contacts Type.', 'caldera-forms-civicrm' ); ?>
		</p>
	</div>
</div>

<!-- Contact Subtype -->
<?php if ( $contact_sub_types['count'] ) : ?>
<div class="caldera-config-group">
	<label for="{{_id}}_contact_sub_type">
		<?php esc_html_e( 'Contact Subtype', 'caldera-forms-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<select class="block-input field-config" name="{{_name}}[contact_sub_type]">
		<option value="" {{#is contact_sub_type value=""}}selected="selected"{{/is}}></option>
		<?php foreach ( $contact_sub_types['values'] as $key => $subtype ) { ?>
			<option value="<?php echo esc_attr( $subtype['name'] ); ?>" {{#is contact_sub_type value="<?php echo $subtype['name']; ?>"}}selected="selected"{{/is}}><?php echo esc_html( $type['label'] ); ?></option>
		<?php } ?>
		</select>
		<p class="description" id="{{_id}}_contact_sub_type">
			<?php esc_html_e( 'Limit by Contact Subtype.', 'caldera-forms-civicrm' ); ?>
		</p>
	</div>
</div>
<?php endif; ?>

<!-- Groups -->
<div class="caldera-config-group">
	<label for="{{_id}}_civicrm_group">
		<?php esc_html_e( 'Groups', 'caldera-forms-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<select id="{{_id}}_civicrm_group" class="block-input field-config" name="{{_name}}[civicrm_group]" nonce="<?php echo wp_create_nonce('admin_get_groups'); ?>">
		</select>
		<p class="description" id="{{_id}}_civicrm_group">
			<?php esc_html_e( 'Limit to contacts in groups.', 'caldera-forms-civicrm' ); ?>
		</p>
	</div>
</div>
<script>
	jQuery( document ).ready( function( $ ) {

		if( '{{civicrm_group}}' ) {
			$.ajax( {
				url : ajaxurl,
				type : 'post',
				data : {
					group_id: '{{civicrm_group}}',
					action : 'civicrm_get_groups',
					nonce: $( 'select#{{_id}}_civicrm_group' ).attr( 'nonce' )
				},
				success : function( response ) {
					var result = JSON.parse( response );
					var data = {
						id: result[0]['id'],
						text: result[0]['title']
					};
					$( 'select#{{_id}}_civicrm_group' )
						.append( new Option( data.text, data.id, false, false ) )
						.trigger( 'change' );
				}
			} );
		}

		$( 'select#{{_id}}_civicrm_group' ).cfcSelect2( {
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				type: 'post',
				delay: 250,
				data: function ( params ) {
					return {
						search: params.term,
						action: 'civicrm_get_groups',
						nonce: $( 'select#{{_id}}_civicrm_group' ).attr( 'nonce' )
					};
				},
				processResults: function( data ) {
					var options = [];
					if ( data ) {
						$.each( data, function( index, group ) {
							options.push( { id: group['id'], text: group['title'] } );
						});
					}
					return {
						results: options
					};
				},
			},
			width: '100%',
			allowClear: true,
			placeholder: 'Search groups',
		});
	} );
</script>

