function cfc_contact_link_options( id ) {

	var options = [];
	for ( var i = 1; i <= 10; i++ ) {
		options.push( { id: 'contact_' + i, text: 'Contact ' + i } );
	}
	// add empty option for placeholder to work
	options.unshift( {id: '', text: '' } )

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

	jQuery( selector ).cfcSelect2({
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
			.cfcSelect2( {
				data: options,
				placeholder: 'Select Contact processor',
				width: '100%'
			} ).on( 'select2:select', function( e ) {
				jQuery( selector )
					.empty()
					.append( new Option( e.params.data.text, e.params.data.id, false, false ) )
					.cfcSelect2( 'close' );
			} )
	});
}

// decorate formJSON to add price field options for autopopulate field conditionals
jQuery( document ).ready( function( $ ) {

	$.fn.originalFormJSON = $.fn.formJSON;

	$.fn.formJSON = function() {

		var form = $( this ).originalFormJSON();

		if ( ! form.config || ! form.config.fields ) return form;

		for ( var field_id in form.config.fields ) {

			var config = form.config.fields[field_id].config;

			if ( config.auto && ( config.auto_type.indexOf( 'price_field_' ) !== -1 || config.auto_type.indexOf( 'custom_' ) !== -1 ) ) {

				form.config.fields[field_id].config.option = {};
				// cfc_price_field_<id> or custom_<id>
				var preset_name = config.auto_type.replace( 'cfc_', '' ),
					options = preset_options[preset_name].data;

				if ( options.constructor !== Array ) return form;
				options.map( function( option ) {

					var parts = option.split( '|' );

					form.config.fields[field_id].config.option[parts[0]] = {
						value: parts[0],
						label: parts[1],
						calc_value: parts[2]
					};

				} );
			}
		}

		return form;
	}
} );
