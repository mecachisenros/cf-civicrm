jQuery( document ).on( 'cfc.discount.apply', function ( event, data ) {

	var state = cfstate[data.form_id];

	for ( option_id in data.options ) {

		var option = data.options[option_id],
		field_id = option.field_id + '_' + data.instance;

		state.mutateState( field_id, option.value );

		state.rebind( field_id );

	}

} );
