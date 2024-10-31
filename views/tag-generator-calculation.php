<div class="control-box">
	<fieldset>
		<legend>
			<?php
			esc_html_e( 'Create a read-only form input with a value that\'s automatically calculated based on user input in another field.', 'pvb-cf7-calculator' );
			echo ' ';
			printf(
				/* translators: %s placeholder replaced with link to documentation */
				esc_html__( 'For more details, see %s' ),
				'<a href="http://bossakov.eu/documentation/pvb-cf7-calculator-' .
					esc_attr( $plugin_version ) .
					'.pdf" target="_blank">' .
					esc_html__( 'plugin documentation', 'pvb-cf7-calculator' ) .
					'</a>'
			);
			?></legend>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php esc_html_e( 'Name', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php esc_html_e( 'Id attribute', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php esc_html_e( 'Class attribute', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-min' ); ?>"><?php esc_html_e( 'Minimum value', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="number" name="min" class="minvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-min' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-max' ); ?>"><?php esc_html_e( 'Maximum value', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="number" name="max" class="maxvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-max' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-precision' ); ?>"><?php esc_html_e( 'Result precision (digits after decimal point)', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="number" name="precision" class="precisionvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-precision' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Hide field', 'pvb-cf7-calculator' ); ?>
					</th>
					<td>
						<label><input type="checkbox" name="cf7-hide" class="option" value="on"> <?php esc_html_e( 'Hide field', 'pvb-cf7-calculator' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php esc_html_e( 'Calculation formula', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<p class="cf7-container-tags">
							<strong>
								<?php
								$form_tags = wpcf7_scan_form_tags();
								foreach ( $form_tags as $form_tag ) {
									if ( ! empty( $form_tag['name'] ) ) {
										echo '[', esc_html( $form_tag['name'] ), '] ';
									}
								}
								?>
							</strong>
						</p>
						<textarea rows="3" class="large-text code" name="values" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"></textarea><br>
						<?php esc_html_e( 'Example: (field1 + field2) * 1.2', 'pvb-cf7-calculator' ); ?><br>
						</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo esc_attr( $type ); ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert', 'pvb-cf7-calculator' ) ); ?>" />
	</div>

	<br class="clear" />
</div>
