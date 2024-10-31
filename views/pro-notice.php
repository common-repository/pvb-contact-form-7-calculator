<div class="notice notice-info">
	<p style="min-height:64px">
		<img src="<?php
		echo esc_attr( plugins_url( 'images/icon.png', dirname( __FILE__ ) ) );
		?>" style="float:left; margin-right: 1em; margin-bottom: 3em">
		<?php
		echo sprintf(
			esc_html(
				/* translators: %s placeholder replaced with link to pro version purchase page */
				__(
					'If you like PVB Contact Form 7 Calculator, please consider upgrading to our %s. It lets you have multiple calculated fields per form, perform calculations based on text values (such as drop-down menus), integrate your calculator with PayPal, Stripe and Google Sheets, and other great features scheduled for future releases.',
					'pvb-cf7-calculator'
				)
			),
			'<a href="https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/" target="_blank">' .
				esc_html__( 'Pro version', 'pvb-cf7-calculator' ) .
				'</a>'
		);
		?>
		<br>
		<a href="https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/">
			<?php esc_html_e( 'Get Pro version', 'pvb-cf7-calculator' ); ?>
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
