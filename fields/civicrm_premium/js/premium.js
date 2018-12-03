jQuery( function( $ ) {
	$( 'body' ).on( 'click', '.cf-toggle-group-premium a', function(){

		var clicked = $( this ),
		parent = clicked.closest( '.caldera-config-field' ),
		input = parent.find( '[data-ref="' + clicked.attr( 'id' ) + '"]' ),
		premium_wrapper = parent.find( '.premium-wrapper' );

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
} );