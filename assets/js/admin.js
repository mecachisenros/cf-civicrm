function cfc_select2_defaults( selector, value ){

    if ( value ) {
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
                var default_option = new Option( data.text, data.id, false, false );
                jQuery( selector ).append( default_option ).trigger( 'change' );
            }
        });
    }

    jQuery( selector ).civiSelect2();

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
        placeholder: 'Search for a Contact',
    });
}