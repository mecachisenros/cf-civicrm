<?php

/**
 * Notices template.
 * 
 * Contains a reference to the form configuration.
 * @since 1.0
 * @param array $data The data array
 * @param object $plugin Reference to this plugin  
 */

$notices = apply_filters( 'cfc_notices_to_render', [], $data, $plugin );

?>
<div class="<?php esc_html_e( 'cfc-notices-' . $data['form']['ID'] ); ?>">
	<?php if ( ! empty( $notices ) ): ?>
		<?php foreach ( $notices as $notice ): ?>
			<div class="caldera-grid">
				<div class="alert alert-<?php echo esc_attr( $notice['type'] ); ?>">
					<?php echo $notice['note']; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>