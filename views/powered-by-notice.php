<div class="notice notice-info">
	<p style="min-height:64px">
		<img src="<?php
		echo esc_attr( plugins_url( 'images/icon.png', dirname( __FILE__ ) ) );
		?>" style="float:left; margin-right: 1em; margin-bottom: 3em">
		<?php
		echo sprintf(
			esc_html(
				/* translators: %s placeholder replaced with link to settings page */
				__(
					'Care to support the development of PVB Contact Form 7 Calculator? Please consider enabling a small &quot;Powered By&quot; notice on your contact forms with a link to our plugin on wordpress.org. You can always disable that from the %s if you change your mind.',
					'pvb-cf7-calculator'
				)
			),
			'<a href="' . esc_attr( $settings_url ) . '">' . esc_html__( 'Calculator Settings', 'pvb-cf7-calculator' ) . '</a>'
		);
		?>
		<br>
		<a href="#"
			onclick="pvbCf7CalculatorPoweredByEnable(
				this,
				'<?php esc_attr_e( 'Activating...', 'pvb-cf7-calculator' ); ?>',
				'<?php esc_attr_e( 'Thank you!', 'pvb-cf7-calculator' ); ?>'
				)">
			<?php esc_html_e( 'Activate link', 'pvb-cf7-calculator' ); ?>
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



