<div class="control-box">
	<fieldset>
		<legend>
			<?php
			esc_html_e( 'Create a button that fills any calculated values when clicked.', 'pvb-cf7-calculator' );
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
						<label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php esc_html_e( 'Label', 'pvb-cf7-calculator' ); ?></label>
					</th>
					<td>
						<input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" />
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
