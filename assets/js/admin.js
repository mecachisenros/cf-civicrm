function cfc_contact_link_options( id = null ) {

	var options = [];
	for ( var i = 1; i <= 10; i++ ) {
		options.push( { id: 'contact_' + i, text: 'Contact ' + i } );
	}
	
	if( ! id ) return options;

	return options.filter( function( option ) {
		return option.id == id;
	} ).shift();
}

function cfc_select2_defaults( selector, value ) {

	if ( value ) {
		if ( value.indexOf( 'contact_' ) !== -1 ) {
			var option = cfc_contact_link_options( value );
			jQuery( selector )
				.append( new Option( option.text, option.id, false, false ) )
				.trigger( 'change' );
		} else {
			jQuery.ajax({
				url : ajaxurl,
				type : 'post',
				data : {
					contact_id: value,
					action : 'civicrm_get_contacts',
					nonce: jQuery( selector ).attr( 'nonce' )
				},
				success : function( response ) {
					var result = JSON.parse( response );

					var data = {
						id: result[0]['id'],
						text: result[0]['sort_name']
					};
					jQuery( selector )
						.append( new Option( data.text, data.id, false, false ) )
						.trigger( 'change' );
				}
			});
		}
	}

    jQuery( selector ).civiSelect2({
		ajax: {
			url: ajaxurl,
			dataType: 'json',
			type: 'post',
			delay: 250,
			data: function ( params ) {
				return {
					search: params.term,
					action: 'civicrm_get_contacts',
					nonce: jQuery( selector ).attr( 'nonce' )
				};
			},
			processResults: function( data ) {
				var options = [];
				if ( data ) {
					jQuery.each( data, function( index, contact ) {
						options.push( { id: contact['id'], text: contact['sort_name'] } );
					});
				}
				return {
					results: options
				};
			},
			cache: true
		},
		minimumInputLength: 3,
		allowClear: true,
		placeholder: 'Search Contacts or link to a Contact processor',
	});

	jQuery( selector ).on( 'select2:open', function() {
		if ( jQuery( selector + '_contact_link' ).length == 0 ) {
			jQuery( this ).data( 'select2' ).dropdown.$dropdown.append( 
				'<strong style="padding: 6px;">Or link to a Contact processor</strong>' +
				'<div style="padding: 6px;"><select style="padding: 6px;" id="' + 
				selector.replace( '#', '' ) + '_contact_link"></select></div>' );
		}

		var options = cfc_contact_link_options()
		var selected_options = options.map( function( option, index ) {
			if ( option.id == value ) options[index]['selected'] = true; 
		} );

		jQuery( selector + '_contact_link' )
			.civiSelect2( { 
				data: options, 
				placeholder: 'Select Contact processor'
			} )
			.on( 'select2:select', function( e ) {
				jQuery( selector )
					.empty()
					.append( new Option( e.params.data.text, e.params.data.id, false, false ) )
					.trigger( 'change' );
			} );
    });
}