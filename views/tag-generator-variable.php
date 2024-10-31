<div class="control-box">
	<fieldset>
		<legend>
			<?php
			esc_html_e( 'Create a read-only form input with a number that\'s assigned based on another field\'s value.' );
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
			?>
		</legend>
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
						<?php esc_html_e( 'Hide field', 'pvb-cf7-calculator' ); ?>
					</th>
					<td>
						<label><input type="checkbox" name="cf7-hide" class="option" value="on"> <?php esc_html_e( 'Hide field', 'pvb-cf7-calculator' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php esc_html_e( 'Number equivalents', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<textarea rows="3" class="large-text code" name="values" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"></textarea><br>
						<?php esc_html_e( 'Example:', 'pvb-cf7-calculator' ); ?><br>
						<em><?php esc_html_e( 'city:', 'pvb-cf7-calculator' ); ?><br>
						<?php esc_html_e( 'New York 100', 'pvb-cf7-calculator' ); ?><br>
						<?php esc_html_e( 'London 150', 'pvb-cf7-calculator' ); ?><br>
						<?php esc_html_e( 'Tokyo 200', 'pvb-cf7-calculator' ); ?><br></em>
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
