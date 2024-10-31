<div class="notice notice-info">
	<p style="min-height:64px">
		<img src="<?php
		echo esc_attr( plugins_url( 'images/icon.png', dirname( __FILE__ ) ) );
		?>" style="float:left; margin-right: 1em; margin-bottom: 3em">
		<?php
		echo sprintf(
			esc_html(
				/* translators: %s placeholder replaced with link to plugin review page */
				__(
					'How is your experience with PVB Contact Form 7 Calculator? Please consider %s.',
					'pvb-cf7-calculator'
				)
			),
			'<a href="https://wordpress.org/support/plugin/pvb-contact-form-7-calculator/reviews/" target="_blank">' .
				esc_html__( 'leaving a review on wordpress.org', 'pvb-cf7-calculator' ) .
				'</a>'
		);
		?>
		<br>
		<a href="https://wordpress.org/support/plugin/pvb-contact-form-7-calculator/reviews/">
			<?php esc_html_e( 'Review plugin', 'pvb-cf7-calculator' ); ?>
		</a>
		<br>
		<a href="#" onclick="pvbCf7CalculatorHideAdminNotices(1, this)">
			<?php esc_html_e( 'Remind me later', 'pvb-cf7-calculator' ); ?>
		</a>
		<br>
		<a href="#" onclick="pvbCf7CalculatorHideAdminNotices(2, this)">
			<?php esc_html_e( 'Do not show this again', 'pvb-cf7-calculator' ); ?>
		</a>        
	</p>
</div>



