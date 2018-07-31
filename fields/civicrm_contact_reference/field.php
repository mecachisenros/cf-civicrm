<?php 
	echo $wrapper_before;
	if ( isset( $field[ 'slug' ] ) && isset( $_GET[ $field[ 'slug' ] ] ) ) {
		$field_value = Caldera_Forms_Sanitize::sanitize( $_GET[ $field[ 'slug' ] ] );
	}

	$placeholder = '';
	if( !empty( $field['config']['placeholder'] ) ){
		$placeholder = 'data-placeholder="' . esc_attr( Caldera_Forms::do_magic_tags( $field['config']['placeholder'] ) ). '"';
	}
?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<select <?php echo $field_placeholder; ?> id="<?php echo esc_attr( $field_id ); ?>" data-field="<?php echo esc_attr( $field_base_id ); ?>" class="<?php echo esc_attr( $field_class ); ?>" nonce="<?php echo wp_create_nonce('civicrm_contact_reference_get'); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo $field_required; ?> <?php echo $placeholder; ?>>
		</select>
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>

<!-- <?php $field['config']['default']; ?> -->
<script>
	jQuery( function( $ ) {

		if( '<?php echo esc_attr( $field['config']['default'] ); ?>' ) {
			$.ajax( {
				url : cfc.url,
				type : 'post',
				data : {
					contact_id: '<?php echo esc_attr( $field['config']['default'] ); ?>',
					action : 'civicrm_contact_reference_get',
					nonce: $( '#<?php echo esc_attr( $field_id ); ?>' ).attr( 'nonce' )
				},
				success : function( response ) {
					var result = JSON.parse( response );
					var data = {
						id: result[0]['id'],
						text: result[0]['sort_name']
					};
					$( '#<?php echo esc_attr( $field_id ); ?>' )
						.append( new Option( data.text, data.id, false, false ) )
						.trigger( 'change' );
				}
			} );
		}
		$( '#<?php echo esc_attr( $field_id ); ?>' ).cfcSelect2( {
	  		ajax: {
    			url: cfc.url,
    			dataType: 'json',
    			type: 'post',
    			delay: 250,
    			data: function ( params ) {
      				return {
        				search: params.term,
        				action: 'civicrm_contact_reference_get',
        				field_id: $( '#<?php echo esc_attr( $field_id ); ?>' ).data( 'field' ),
        				form_id: '<?php echo esc_attr( $form['ID'] ); ?>',
        				nonce: $( '#<?php echo esc_attr( $field_id ); ?>' ).attr( 'nonce' )
      				};
    			},
    			processResults: function( data ) {
					var options = [];
					if ( data ) {
						$.each( data, function( index, contact ) {
							options.push( { id: contact['id'], text: contact['sort_name'] } );
						});
					}
					return {
						results: options
					};
				},
			},
			allowClear: true,
			placeholder: '<?php echo $placeholder; ?>',
		} );
	} );
</script>