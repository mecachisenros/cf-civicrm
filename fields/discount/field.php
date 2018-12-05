<?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<?php echo Caldera_Forms_Field_Input::html( $field, $field_structure, $form ); ?>
		<button 
			style="margin-top: 10px;"
			id="<?php echo esc_attr( $field_id ); ?>_cividiscount_button"
			type="button"
			class="btn btn-block"
			data-post-id="<?php echo get_the_ID(); ?>"
			data-nonce="<?php echo wp_create_nonce( 'civicrm_cividiscount_code' ); ?>"
			>
			Apply discount
			</button>
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>

<?php ob_start(); ?>
<script>
	jQuery( function( $ ) {

		var discount_button = $( '#<?php echo esc_attr( $field_id ); ?>_cividiscount_button' );

		$( discount_button ).on( 'click', function( e ) {
			e.preventDefault();

			// var field_id = '<?php echo esc_attr( $field_id ); ?>',
			var code = $( '#<?php echo esc_attr( $field_id ); ?>' ).val(),
			discount_button = $( '#<?php echo esc_attr( $field_id ); ?>_cividiscount_button' ),
			form_id = '<?php echo esc_attr( $form['ID'] ); ?>',
			form_id_attr = '<?php echo esc_attr( $form['ID'] . '_' . Caldera_Forms_Render_Util::get_current_form_count() ); ?>';

			if ( ! code ) return;
			
			$.ajax( {
				url: cfc.url,
				type: 'post',
				data: {
					cividiscount_code: code,
					action: 'do_code_cividiscount',
					post_id: discount_button.data( 'post-id' ),
					form_id: form_id,
					form_id_attr: form_id_attr,
					nonce: discount_button.data( 'nonce' )
				},
				success: function( response ) {

					var options = JSON.parse( response );
					for ( var option_id in options ) {
						// option
						var option = options[option_id];

						// calc value
						$( '#' + option_id ).attr( 'data-calc-value', option.calc_value );
						$( '#' + option_id ).parent().attr( 'data-label', option.label );
						// label text
						$( '#' + option_id ).parent().contents().map( function( el ) {
							// replace text node (type 3), the label
							if ( this.nodeType == 3 ) {
								this.textContent = ' ' + option.label;
							}
						} );

					}

				}

			} );

		} );

	} );
</script>
<?php
	$script_template = ob_get_clean();
	if ( ! empty( $form[ 'grid_object' ] ) && is_object( $form[ 'grid_object' ] ) ) {
		$form[ 'grid_object' ]->append( $script_template, $field[ 'grid_location' ] );
	} else {
		echo $script_template;
	}
