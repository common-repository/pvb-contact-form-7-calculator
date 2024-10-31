<?php
/**
 * WordPress Settings Framework
 *
 * @author  Gilbert Pellegrom, James Kemp
 * @link    https://github.com/gilbitron/WordPress-Settings-Framework
 * @license MIT
 */

/**
 * Define your settings
 *
 * The first parameter of this filter should be wpsf_register_settings_[options_group],
 * in this case "pvb_cf7_calculator_settings_general".
 *
 * Your "options_group" is the second param you use when running new WordPressSettingsFramework()
 * from your init function. It's important as it differentiates your options from others.
 *
 * To use the tabbed example, simply change the second param in the filter below to 'wpsf_tabbed_settings'
 * and check out the tabbed settings function on line 156.
 */
add_filter(
	'wpsf_register_settings_pvb_cf7_calculator_settings_general',
	'pvb_cf7_calculator_tabless_settings_free'
);

function pvb_cf7_calculator_tabless_settings_free( $wpsf_settings ) {
	// General Settings section
	$wpsf_settings[] = array(
		'section_id'          => 'features',
		'section_title'       => __( 'Features', 'pvb-cf7-calculator' ),
		'section_description' => __(
			'Here you can manage calculator features.',
			'pvb-cf7-calculator'
		),
		'section_order'       => 1, // The order of the section (required)
		'fields'              => array(
			array(
				'id'      => 'show_powered_by',
				'title'   => __(
					'Show &quot;Powered By&quot; notice',
					'pvb-cf7-calculator'
				),
				'desc'    => __(
					'Show a small link to this plugin on wordpress.org on each contact form on your website.',
					'pvb-cf7-calculator'
				),
				'type'    => 'radio',
				'choices' => array(
					'0' => 'Off',
					'1' => 'On',
				),
				'default' => '0',
			),
			array(
				'id'      => 'hide_promo_admin_notices',
				'title'   => __(
					'Promotional notices in admin area',
					'pvb-cf7-calculator'
				),
				'type'    => 'radio',
				'choices' => array(
					'0' => __( 'Visible', 'pvb-cf7-calculator' ),
					'1' => __( 'Temporarily hidden', 'pvb-cf7-calculator' ),
					'2' => __( 'Permanently hidden', 'pvb-cf7-calculator' ),
				),
				'default' => '0',
			),
			array(
				'id'         => 'hide_promo_admin_notices_until',
				'title'      => __(
					'Promotional notices in admin area hidden until',
					'pvb-cf7-calculator'
				),
				'desc'       => __(
					'This option will take effect if the above selection is &quot;Temporarily hidden&quot;',
					'pvb-cf7-calculator'
				),
				'type'       => 'date',
				'datepicker' => array( 'dateFormat' => 'yy-mm-dd' ),    // Array of datepicker options
																		// (http://api.jqueryui.com/datepicker/)
			),
		),
	);

	return $wpsf_settings;
}
