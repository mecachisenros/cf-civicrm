jQuery( function( $ ) {
	$( 'body' ).on( 'click', '.cf-toggle-group-premium a', function(){

		var clicked = $( this ),
		parent = clicked.closest( '.caldera-config-field' ),
		input = parent.find( '[data-ref="' + clicked.attr( 'id' ) + '"]' ),
		premium_wrapper = parent.find( '.premium-wrapper' ),
		calc = parent.find( '.premium-calc' );

		parent.find( '.btn' ).removeClass( clicked.data( 'active' ) ).addClass( clicked.data( 'default' ) );
		clicked.addClass( clicked.data( 'active' ) ).removeClass( clicked.data( 'default' ) );
		input.prop( 'checked', true ).trigger( 'change' );

		if ( clicked.attr( 'id' ).indexOf( 'premium' ) !== -1 ) {
			premium_wrapper.find( '.premium-mini' ).hide();
			premium_wrapper.find( '.premium-full' ).show();
		} else {
			premium_wrapper.find( '.premium-mini' ).show();
			premium_wrapper.find( '.premium-full' ).hide();
		}

	} );

	$( 'body' ).on( 'change', '[data-type="calculation"]', function( e ) {
		var calc_field_id = $( this ).attr( 'data-calc-field' ),
			min = $( '.premium' ).find( '[data-calc-field-id="' + calc_field_id + '"]' ),
			toggles = $( '.cf-toggle-group-premium' ).find( '[data-field="' + min.attr( 'data-field-id' ) + '"]' );

		if ( parseFloat( $( this ).val() ) >= parseFloat( min.val() ) ) {
			toggles.map( function( index, element ) {
				$( element ).attr( 'disabled', false );
			} );
		} else {
			toggles.map( function( index, element ) {
				$( element ).attr( 'disabled', true );
			} );
		}
	} );
} );