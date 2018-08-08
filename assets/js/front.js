jQuery( document ).on( 'cf.form.submit', function ( event, data ) {
    var $form = data.$form,
    form_id = $form.data( 'form-id' );
    // remove membership notices
    jQuery( '.cfc-notices-' + form_id ).remove();
});