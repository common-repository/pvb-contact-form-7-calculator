<div class="notice notice-warning">
	<p>
		<?php
		echo esc_html(
			__(
				'You have activated both the Free and Pro versions of the PVB Contact Form 7 Calculator plugin. To avoid potential resource conflicts, please deactivate the free version.',
				'pvb-cf7-calculator'
			)
		);
		?>
		<br>
		<a href="<?php echo esc_attr( admin_url( 'plugins.php' ) ); ?>">
			<?php esc_html_e( 'Manage Plugins', 'pvb-cf7-calculator' ); ?>
		</a>
	</p>
</div>
