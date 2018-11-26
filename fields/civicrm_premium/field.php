<?php $disabled = $field['config']['min_clean'] > 0 ? 'disabled="true"' : 'disabled="false"'; ?>
<?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<div class="caldera-config-field init_field_type premium" data-type="toggle_button">
			<div class="cf-toggle-group-premium btn-group" style="width: 100%; clear: both;">
				<a 
					style="width: 50%;" 
					id="<?php echo esc_attr( $field_id ); ?>_premium" 
					data-label="<?php echo esc_attr( $field['config']['name'] );?>" 
					data-field="<?php echo esc_attr( $field_base_id ); ?>" 
					data-active="<?php echo esc_attr( $field['config']['active_class'] ); ?>" 
					data-default="<?php echo esc_attr( $field['config']['default_class'] ); ?>" 
					class="btn <?php echo esc_attr( $field['config']['default_class'] ); ?>" 
					data-value="<?php echo esc_attr( $field['config']['premium_id'] ); ?>" 
					<?php echo $disabled; ?> 
					<?php echo $field_structure['aria']; ?> 
					title="<?php echo esc_attr( $field['config']['name'] ); ?>">
						<?php echo esc_html( $field['config']['name'] ); ?>
				</a>
				<a 
					style="width: 50%;" 
					id="<?php echo esc_attr( $field_id ); ?>_no_thank_you" 
					data-label="No thank you" 
					data-field="<?php echo esc_attr( $field_base_id ); ?>" 
					data-active="<?php echo esc_attr( $field['config']['active_class'] ); ?>" 
					data-default="<?php echo esc_attr( $field['config']['default_class'] ); ?>" 
					class="btn <?php echo esc_attr( $field['config']['default_class'] ); ?>" 
					data-value="0"
					<?php echo $disabled; ?> 
					<?php echo $field_structure['aria']; ?> 
					title="<?php echo esc_attr( $field['config']['no_thanks'] ); ?>">
						<?php echo esc_attr( $field['config']['no_thanks'] ); ?>
				</a>
			</div>
			<div style="display: none;" aria-hidden="true">
				<input 
					<?php if ( ! empty( $field['required'] ) ) { ?>required="required"<?php } ?> 
					type="radio" 
					id="<?php echo esc_attr( $field_id ); ?>_premium" 
					data-label="<?php echo esc_attr( $field['config']['name'] );?>" 
					data-field="<?php echo esc_attr( $field_base_id ); ?>" 
					data-ref="<?php echo esc_attr( $field_id ); ?>_premium" 
					class="cf-toggle-group-radio <?php echo $field_id; ?>" 
					name="<?php echo esc_attr( $field_name ); ?>" 
					value="<?php echo esc_attr( $field['config']['premium_id'] ); ?>" 
					data-radio-field="<?php echo esc_attr( $field_id ); ?>" 
				>
				<input 
					<?php if ( ! empty( $field['required'] ) ) { ?>required="required"<?php } ?> 
					type="radio" 
					id="<?php echo esc_attr( $field_id ); ?>_no_thank_you" 
					data-label="No thank you" 
					data-field="<?php echo esc_attr( $field_base_id ); ?>" 
					data-ref="<?php echo esc_attr( $field_id ); ?>_no_thank_you" 
					class="cf-toggle-group-radio <?php echo $field_id; ?>" 
					name="<?php echo esc_attr( $field_name ); ?>" 
					value="0" 
					data-radio-field="<?php echo esc_attr( $field_id ); ?>" 
				>
				<input 
					type="hidden" 
					id="<?php echo esc_attr( $field_id ); ?>_calc"
					data-field-id="<?php echo esc_attr( $field_base_id ); ?>" 
					data-min="<?php echo esc_attr( $field['config']['min_clean'] );?>" 
					data-calc-field-id="<?php echo esc_attr( $field['config']['calc'] ); ?>" 
					class="premium-calc" 
					name="<?php echo esc_attr( $field_base_id ); ?>_min"
					value="<?php echo esc_attr( $field['config']['min_clean'] );?>"  
				>
			</div>
			<!-- Premium -->
			<div id="<?php echo esc_attr( $field_id ); ?>_premium" class="row premium-wrapper" style="margin-top: 20px;">
				<div class="premium-mini">
					<?php if ( $field['config']['image'] ): ?>
						<div class="col-sm-2 first_col premium-image">
							<img src="<?php echo get_site_url( null, $field['config']['thumbnail'] ); ?>" alt="<?php echo esc_attr( $field['config']['name'] ) ?>">
						</div>
					<?php endif; ?>
					<div class="col-sm-<?php $field['config']['image'] ? print( '10 last_col' ) : print( '12' ); ?>">
						<div class="premium-details">
							<p>
								<span class="premium-name" style="display: block; font-weight: 700;"><?php echo esc_html( $field['config']['name'] ); ?></span>
								<span class="help-block"><?php echo esc_html( $field['config']['min'] ) ?></span>
							</p>
						</div>
					</div>
				</div>
				<div class="premium-full" style="display: none;">
					<?php if ( $field['config']['image'] ): ?>
						<div class="col-sm-4 first_col premium-image">
							<img src="<?php echo get_site_url( null, $field['config']['image'] ); ?>" alt="<?php echo esc_attr( $field['config']['name'] ) ?>">
						</div>
					<?php endif; ?>
					<div class="col-sm-<?php $field['config']['image'] ? print( '8 last_col' ) : print( '12' ); ?>">
						<div class="premium-details">
							<p>
								<span class="premium-name" style="display: block; font-weight: 700;"><?php echo esc_html( $field['config']['name'] ); ?></span>
								<span class="premium-description"><?php esc_html_e( $field['config']['desc'], 'caldera-forms-civicrm' ); ?></span>
							</p>
							<?php if ( $field['config']['options'] ): ?>
								<select class="form-control" name="<?php echo esc_attr( $field_base_id ); ?>_option" id="<?php echo esc_attr( $field_id ); ?>_option">
									<?php foreach ( $field['config']['options'] as $option ): ?>
										<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
									<?php endforeach ?>
								</select>
							<?php endif; ?>
							<div class="help-block"><?php echo esc_html( $field['config']['min'] ) ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php echo $field_caption; ?>
		</div>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>