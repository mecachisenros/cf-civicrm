<!-- Active class -->
<div class="caldera-config-group">
	<label for="{{_id}}_active">
		<?php esc_html_e( 'Toggle active class', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<input id="{{_id}}_active"type="text" value="{{#if active_class}}{{active_class}}{{else}}btn-success{{/if}}" name="{{_name}}[active_class]" class="block-input field-config">
	</div>
	<p class="description">
		<?php _e( 'The selected class of the toggle button.', 'cf-civicrm' ); ?>
	</p>
</div>

<!-- Inactive class -->
<div class="caldera-config-group">
	<label for="{{_id}}_inactive">
		<?php esc_html_e( 'Toggle inactive class', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<input id="{{_id}}_inactive"type="text" value="{{#if default_class}}{{default_class}}{{else}}btn-default{{/if}}" name="{{_name}}[default_class]" class="block-input field-config">
	</div>
	<p class="description">
		<?php _e( 'The default class of the toggle button, ie. not clicked/selected.', 'cf-civicrm' ); ?>
	</p>
</div>

<!-- Premiums -->
<div class="caldera-config-group">
	<label for="{{_id}}_premium_id">
		<?php esc_html_e( 'Premium', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<select id="{{_id}}_premium_id" class="block-input field-config" name="{{_name}}[premium_id]" nonce="<?php echo wp_create_nonce( 'admin_get_premiums' ); ?>">
		</select>
	</div>
	<p class="description">
		<?php _e( 'Select a premium (this field is required).', 'cf-civicrm' ); ?>
	</p>
</div>

<!-- Default -->
<div class="caldera-config-group">
	<label for="{{_id}}_default">
		<?php esc_html_e( 'Default option', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<select id="{{_id}}_default" class="block-input field-config" name="{{_name}}[default]">
			<option value="" {{#is default value=""}}selected="selected"{{/is}}></option>
			<option value="premium" {{#is default value="premium"}}selected="selected"{{/is}}>Premium</option>
			<option value="no_thanks" {{#is default value="no_thanks"}}selected="selected"{{/is}}>No thank you</option>
		</select>
	</div>
	<p class="description">
		<?php _e( 'Select a premium (this field is required).', 'cf-civicrm' ); ?>
	</p>
</div>

<!-- No thanks -->
<div class="caldera-config-group">
	<label for="{{_id}}_no_thanks">
		<?php esc_html_e( 'No thank you label', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<input id="{{_id}}_no_thanks"type="text" value="{{#if no_thanks}}{{no_thanks}}{{else}}No thank you{{/if}}" name="{{_name}}[no_thanks]" class="block-input field-config">
	</div>
</div>

<div class="caldera-config-group">
	<label for="{{_id}}_disable_thank_you">
		<?php esc_html_e( 'Don\'t show \'No thank you\'', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		<input id="{{_id}}_disable_thank_you" type="checkbox" class="field-config" name="{{_name}}[no_no_thanks]" value="1" {{#if no_no_thanks}}checked="checked"{{/if}}>
	</div>
</div>

<!-- Calculation field -->
<div class="caldera-config-group">
	<label for="{{_id}}_calc"">
		<?php _e( 'Calculation field', 'cf-civicrm' ); ?>
	</label>
	<div class="caldera-config-field">
		{{{_field slug="calc"}}}
	</div>
	<p class="description">
		<?php _e( 'Calculation field to check for minimum contribution.', 'cf-civicrm' ); ?>
	</p>
</div>


<script>
	jQuery( document ).ready( function( $ ) {

		if ( '{{premium_id}}' ) {
			$.ajax( {
				url : ajaxurl,
				type : 'post',
				data : {
					premium_id: '{{premium_id}}',
					action : 'civicrm_get_premiums',
					nonce: $( 'select#{{_id}}_premium_id' ).attr( 'nonce' )
				},
				success : function( response ) {
					var result = JSON.parse( response );

					var data = {
						id: result[0]['id'],
						text: result[0]['name']
					};
					$( '#{{_id}}_premium_id' )
						.append( new Option( data.text, data.id, false, false ) )
						.trigger( 'change' );
				}
			} );
		}

		$( '#{{_id}}_premium_id' ).cfcSelect2( {
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				type: 'post',
				delay: 250,
				data: function ( params ) {
					return {
						search: params.term,
						action: 'civicrm_get_premiums',
						nonce: $( 'select#{{_id}}_premium_id' ).attr( 'nonce' )
					};
				},
				processResults: function( data ) {
					var options = [];

					if ( data ) {
						$.each( data, function( index, premium ) {
							options.push( { id: premium['id'], text: premium['name'] } );
						});
					}

					return {
						results: options
					};
				},
			},
			width: '100%',
			placeholder: 'Select a Premium',
		} );

	} );
</script>

