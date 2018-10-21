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

<script>
	jQuery( function( $ ) {


		if( '<?php echo esc_attr( $field['config']['default'] ); ?>' ) {
			$.ajax( {
				url : cfc.url,
				type : 'post',
				data : {
					contact_id: '<?php echo esc_attr( $field['config']['default'] ); ?>',
					action: 'civicrm_contact_reference_get',
					field_id: $( '#<?php echo esc_attr( $field_id ); ?>' ).data( 'field' ),
					form_id: '<?php echo esc_attr( $form['ID'] ); ?>',
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
			<?php if ( isset( $field['config']['new_organization'] ) ): ?>
			language: {
				noResults: function ( params ) {
					var html = '<div>No results found...</div>' +
						'<div class="caldera-grid"><div class="row"><div class="col-sm-9 first_col"><div class="form-group"><div>' +
						'<input placeholder="Organization name" type="text" id="<?php echo esc_attr( $field_id ); ?>_org_name" class="form-control" name="<?php echo esc_attr( $field_id ); ?>_new" value="">' +
						'</div></div></div><div class="col-sm-3 last_col"><div class="form-group"><div>' +
						'<input type="submit" id="<?php echo esc_attr( $field_id ); ?>_btn" name="<?php echo esc_attr( $field_id ); ?>_btn" class="btn btn-primary" value="Add new">' +
						'</div></div></div></div></div>';
					return html;
				}
			},
			escapeMarkup: function ( markup ) {
				if ( markup.indexOf( 'No results found' ) != -1 ) {
					// trigger event to attach click event to 'Add new' input
					$.Event( 'cfc:add-new-rendered' );
					// delay event
					setTimeout( function() { 
						$( 'body' ).trigger( 'cfc:add-new-rendered', '<?php echo esc_attr( $field_id ); ?>' ) 
					}, 1000 );
				}
				return markup;
			}
		<?php endif; ?>
		} );

		// attach click event on cfc:add-new-rendered
		$( 'body' ).on( 'cfc:add-new-rendered', function( e, field_id ) {
			e.preventDefault();
			var add_new = $( '#' + field_id + '_btn');
			$( add_new ).on( 'click', function( e ) {
				var org_name = $( '#' + field_id + '_org_name' ).val();
				if ( org_name ) {
					var option = new Option( org_name, org_name, false, false );
					$( '#' + field_id ).append( new Option( org_name, org_name, false, false ) );
					$( '#' + field_id ).val( org_name ).trigger( 'change' );
					$( '#' + field_id ).cfcSelect2( 'close' );
				}
			} );
		} );
	} );
</script>