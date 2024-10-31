<div class="notice notice-warning">
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s placeholder replaced with the required version of Contact Form 7 */
				__(
					'Contact Form 7 Price Calculator requires Contact Form 7 version %s or newer to be installed and active.',
					'pvb-cf7-calculator'
				),
				self::CF7_VERSION_REQUIRED
			)
		);
		?>
	</p>
</div>
