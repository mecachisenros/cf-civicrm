// processor conditionals, add price field options for autopopulate
jQuery( document ).ready( function( $ ) {
	$( '.caldera-editor-body' ).on( 'change', '.caldera-conditional-field-set', function( e ) {

		var field = $( this ),
			field_compare = field.parent().find( '.compare-type' ),
			type = field.data( 'condition' ),
			pid = field.data( 'id' ),
			name = "config[" + type + "][" + pid + "][conditions][group][" + field.data('row') + "][" + field.data('line') + "]",
			lineid = field.data( 'line' ),
			target = $( '#' + lineid + "_value" ),
			curval = target.find( '.caldera-conditional-value-field' ).first();

		var field_id = this.value,
			form = core_form.formJSON(),
			config = form.config.fields[field_id].config;

		if ( curval.length ) {
			if ( curval.val().length )
				target.data( 'value', curval.val() );
		} else if ( 0 === target.val() ) {
			target.data( 'value', 0 );
		} else if ( '0' === target.val() ) {
			target.data( 'value', '0' );
		}

		field_compare.show();

		if ( config.auto && config.auto_type.indexOf( 'price_field_' ) !== -1 ) {
			var price_field = config.auto_type.replace( 'cfc_', '' ),
				options_rows = preset_options[price_field].data,
				out = '<select name="' + name + '[value]" class="caldera-processor-value-bind caldera-conditional-value-field" data-field="' + field_id + '" style="max-width: 220px; width: 220px;">';
				out += '<option value=""></option>';

			options_rows.map( function( option ) {

				var parts = option.split('|'),
				label = parts[1],
				value = parts[0],
				sel = '';

				if ( target.data( 'value' ) ) {
					if ( target.data( 'value' ).toString() === value )
						sel = ' selected="selected"';
				}

				out += '<option value="' + value + '"' + sel + '>' + label + '</option>';
			} )

			out += '</select>';

		}

		target.html( out );

	} );
} );