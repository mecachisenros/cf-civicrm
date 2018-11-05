<?php if ( $data ): ?>
	<div class="alert alert-<?php echo esc_attr( $data['type'] ); ?>" style="margin-bottom: 0px;">
		<?php echo $data['note']; ?>
	</div>
<?php endif; ?>