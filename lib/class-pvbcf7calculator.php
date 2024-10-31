<?php
/**
 * File: class-pvbcf7calculator.php
 *
 * Defines the PVBCF7Calculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib;

use \WPCF7_ContactForm;
use \WPCF7_FormTag;
use \WPCF7_TagGenerator;
use PVBCF7Calculator\lib\Math\PVBCalculator;
use PVBCF7Calculator\lib\wpSettingsFramework;
use PVBCF7Calculator\lib\wpSettingsFramework\WordPressSettingsFramework;

define( 'PVB_CF7_CALCULATOR_PATH', dirname( dirname( __FILE__ ) ) );

/**
 * Initializes the PVB Contact Form 7 Calculator plugin
 * and provides core functions.
 *
 * @since 1.0.0
 */
class PVBCF7Calculator {

	/**
	 * Minimum Contact Form 7 version required
	 */
	const CF7_VERSION_REQUIRED = '5.0';

	/**
	 * Unique ID string for plugin settings page
	 *
	 * @since 1.0.6
	 */
	const SETTINGS_PAGE = 'pvb_cf7_calculator_settings_general';

	/**
	 * This plugin's current version
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * Placeholder for a WordPressSettingsFramework instance
	 *
	 * @var WordPressSettingsFramework
	 * @since 1.0.6
	 */
	private $wpsf;

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wpcf7_init', array( $this, 'cf7_init' ), 20 );
		add_action( 'wpcf7_admin_init', array( $this, 'admin_init' ), 60 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );

		// Set plugin version.
		$plugin_data = get_file_data(
			PVB_CF7_CALCULATOR_PATH . '/pvb-cf7-calculator.php',
			array( 'Version' => 'Version' ),
			false
		);

		$this->plugin_version = $plugin_data['Version'];

		// Register settings.
		$this->wpsf = new WordPressSettingsFramework(
			PVB_CF7_CALCULATOR_PATH . '/settings/settings-general.php',
			self::SETTINGS_PAGE
		);
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {
		load_plugin_textdomain(
			'pvb-cf7-calculator',
			false,
			PVB_CF7_CALCULATOR_PATH . '/languages'
		);

		add_filter(
			'plugin_action_links_' .
				plugin_basename(
					PVB_CF7_CALCULATOR_PATH . '/pvb-cf7-calculator.php'
				),
			array( $this, 'plugin_action_links' )
		);

		if ( ! $this->check_cf7() ) {
			add_action( 'admin_notices', array( $this, 'not_active_notice' ) );
		} elseif ( $this->check_pro() ) {
			add_action( 'admin_notices', array( $this, 'pro_active_notice' ) );
		} elseif ( ! ( defined( 'DISABLE_NAG_NOTICES' ) && DISABLE_NAG_NOTICES ) ) {
			add_action( 'admin_notices', array( $this, 'pro_notice' ) );
		}
	}

	/**
	 * Sets up the form tags and hooks supported by this plugin
	 *
	 * @since 1.0.0
	 */
	public function cf7_init() {
		wpcf7_add_form_tag(
			array( 'calculation', 'calculation*' ),
			array( $this, 'calculation_tag_handler' ),
			true
		);
		wpcf7_add_form_tag(
			array( 'calculate_button' ),
			array( $this, 'calculate_button_tag_handler' ),
			true
		);
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_scripts' )
		);
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_admin_scripts' )
		);
		add_action(
			'wp_ajax_pvb_calculate',
			array( $this, 'ajax_calculate' )
		);
		add_action(
			'wp_ajax_nopriv_pvb_calculate',
			array( $this, 'ajax_calculate' )
		);
		add_action(
			'wp_ajax_pvb_powered_by_opt_in',
			array( $this, 'powered_by_opt_in' )
		);
		add_action(
			'wp_ajax_pvb_promo_pause',
			array( $this, 'promo_pause' )
		);
		add_action(
			'wp_ajax_pvb_promo_disable',
			array( $this, 'promo_disable' )
		);

		add_filter(
			'wpcf7_form_elements',
			array( $this, 'nonce_output' )
		);

		if ( (int) wpSettingsFramework\wpsf_get_setting(
			self::SETTINGS_PAGE,
			'features',
			'show_powered_by'
		) === 1 ) {
			add_filter(
				'wpcf7_form_elements',
				array( $this, 'powered_by_output' )
			);
		}
		$this->intercept_submit();
	}

	/**
	 * Set up tag generators for the admin area
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		if ( class_exists( 'WPCF7_TagGenerator' ) ) {
			$tag_generator = WPCF7_TagGenerator::get_instance();
			$tag_generator->add(
				'calculation',
				__( 'Calculation', 'pvb-cf7-calculator' ),
				array( $this, 'calculation_tag_generator' )
			);
			$tag_generator->add(
				'calculate_button',
				__( 'Calculate button', 'pvb-cf7-calculator' ),
				array( $this, 'calculate_button_tag_generator' )
			);
		}
	}

	/**
	 * Enqueue front-end javascript
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_style(
			'pvb-cf7-calculator',
			plugins_url(
				'css/pvb-cf7-calculator.css',
				dirname( __FILE__ )
			),
			array(),
			$this->plugin_version
		);

		wp_enqueue_script(
			'pvb-cf7-calculator',
			plugins_url( 'js/pvb-cf7-calculator.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			$this->plugin_version,
			true
		);

		// Provide the script with an URL
		// where form data should be sent for calculation.
		wp_localize_script(
			'pvb-cf7-calculator',
			'frontend_ajax_object',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Enqueue admin javascript
	 *
	 * @since 1.0.1
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'pvb-cf7-calculator-admin',
			plugins_url( 'js/pvb-cf7-calculator-admin.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			$this->plugin_version,
			true
		);
	}

	/**
	 * Display a notice in the admin area if CF7 not installed,
	 * not active, or too old
	 *
	 * @since 1.0.0
	 */
	public function not_active_notice() {
		$this->load_view( 'not-active-notice' );
	}

	/**
	 * Display a notice in the admin area if the premium version of this plugin
	 * is already active
	 *
	 * @since 1.0.9
	 */
	public function pro_active_notice() {
		$this->load_view( 'pro-active-notice' );
	}

	/**
	 * Opt-in for a "Powered by:" link
	 *
	 * @since 1.0.1
	 */
	public function powered_by_opt_in() {
		if ( current_user_can( 'manage_options' ) ) {
			wpSettingsFramework\wpsf_set_setting(
				self::SETTINGS_PAGE,
				'features',
				'show_powered_by',
				1
			);
			wp_send_json( true );
		} else {
			wp_send_json( false );
		}
	}

	/**
	 * Add "Settings" and "Support" links on Plugins page
	 *
	 * @param string[] $links Links to filter.
	 *
	 * @since 1.0.6
	 */
	public function plugin_action_links( $links ) {
		$settings_page_slug = sprintf(
			'%s-settings',
			str_replace( '_', '-', self::SETTINGS_PAGE )
		);
		$links[]            = '<a href="' .
			esc_url( menu_page_url( $settings_page_slug, false ) ) .
			'">Settings</a>';
		$links[]            = '<a href="https://wordpress.org/support/plugin/pvb-contact-form-7-calculator/"' .
			' target="_blank">Support</a>';
		return $links;
	}

	/**
	 * Adds a nonce for AJAX calculation requests
	 *
	 * @param string $form_elements Form elements to filter.
	 *
	 * @since 1.0.6
	 */
	public function nonce_output( $form_elements ) {
		return $form_elements .
			'<input type="hidden" name="_pvb_ajax_calc_nonce" value="' .
			esc_attr( wp_create_nonce( 'pvb-ajax-calc-nonce' ) ) . '">';
	}

	/**
	 * Filter that outputs "Powered by:" link (only if admin opted in)
	 *
	 * @param string $form_elements Form elements to filter.
	 *
	 * @since 1.0.1
	 */
	public function powered_by_output( $form_elements ) {
		return $form_elements . '<p><small>Powered by <a href="' .
			'https://wordpress.org/plugins/pvb-contact-form-7-calculator/' .
			'" target="_blank">PVB Contact Form 7 Calculator</a>';
	}

	/**
	 * Dismiss promotional notices in the admin area for 2 days
	 *
	 * @since 1.0.6
	 */
	public function promo_pause() {
		// Make sure the AJAX request was sent by an authorized user.
		if ( current_user_can( 'manage_options' ) ) {
			wpSettingsFramework\wpsf_set_setting(
				self::SETTINGS_PAGE,
				'features',
				'hide_promo_admin_notices',
				1
			);
			wpSettingsFramework\wpsf_set_setting(
				self::SETTINGS_PAGE,
				'features',
				'hide_promo_admin_notices_until',
				gmdate( 'Y-m-d', strtotime( '+2 days' ) )
			);
			wp_send_json( true );
		} else {
			wp_send_json( false );
		}
	}

	/**
	 * Dismiss promotional notices in the admin area forever
	 *
	 * @since 1.0.6
	 */
	public function promo_disable() {
		// Make sure the AJAX request was sent by an authorized user.
		if ( current_user_can( 'manage_options' ) ) {
			wpSettingsFramework\wpsf_set_setting(
				self::SETTINGS_PAGE,
				'features',
				'hide_promo_admin_notices',
				2
			);
			wp_send_json( true );
		} else {
			wp_send_json( false );
		}
	}

	/**
	 * Display a promotional notice in the admin area
	 *
	 * @since 1.0.0
	 */
	public function pro_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			// Only show to admins.
			return;
		}

		$hide_option = wpSettingsFramework\wpsf_get_setting(
			self::SETTINGS_PAGE,
			'features',
			'hide_promo_admin_notices'
		);

		if ( 1 === (int) $hide_option &&
			strtotime(
				wpSettingsFramework\wpsf_get_setting(
					self::SETTINGS_PAGE,
					'features',
					'hide_promo_admin_notices_until'
				)
			) > time() ) {
			// Notifications temporarily dismissed.
			return;
		}
		if ( 2 === (int) $hide_option ) {
			// Notifications permanently dismissed.
			return;
		}
		$notices = array( 'pro-notice', 'review-notice' );
		if ( 1 !== (int) wpSettingsFramework\wpsf_get_setting(
			self::SETTINGS_PAGE,
			'features',
			'show_powered_by'
		) ) {
			$notices[] = 'powered-by-notice';
		}

		$settings_page_slug = sprintf(
			'%s-settings',
			str_replace( '_', '-', self::SETTINGS_PAGE )
		);
		$settings_url       = esc_url( menu_page_url( $settings_page_slug, false ) );

		$view = $notices[ intval( wp_rand( 0, count( $notices ) - 1 ) ) ];
		$this->load_view( $view, array( 'settings_url' => $settings_url ) );
	}

	/**
	 * Add a settings page to the admin menu
	 *
	 * @since 1.0.6
	 */
	public function settings_menu() {
		$this->wpsf->add_settings_page(
			array(
				'parent_slug' => 'wpcf7',
				'page_title'  => __( 'Calculator Settings', 'pvb-cf7-calculator' ),
				'menu_title'  => __( 'Calculator Settings', 'pvb-cf7-calculator' ),
				'capability'  => 'manage_options',
			)
		);
	}

	/**
	 * Display a dialog to generate a "calculation" field in the form
	 *
	 * @param WPCF7_ContactForm $contact_form The contact form.
	 * @param array             $options Tag generator options.
	 *
	 * @since 1.0.0
	 */
	public function calculation_tag_generator( $contact_form, $options ) {
		$data = array(
			'args' => wp_parse_args( $options, array() ),
			'type' => 'calculation',
		);
		$this->load_view( 'tag-generator-calculation', $data );
	}

	/**
	 * Process "calculation" form tags
	 *
	 * @param WPCF7_FormTag $tag The form tag to process.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function calculation_tag_handler( $tag ) {
		static $called = false;
		$tag           = new WPCF7_FormTag( $tag );
		if ( empty( $tag->name ) || $called ) {
			return '';
		} else {
			$called = true;
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-calculation' );

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = array();

		$atts['size']      = $tag->get_size_option( '40' );
		$atts['maxlength'] = $tag->get_maxlength_option();
		$atts['minlength'] = $tag->get_minlength_option();

		if ( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ) {
			unset( $atts['maxlength'], $atts['minlength'] );
		}

		$atts['class'] = $tag->get_class_option( $class );

		$cf_label = false;

		if ( $tag->has_option( 'cf7-hide' ) ) {
			$atts['class'] .= ' pvb-display-none';
		}

		$atts['class']   .= ' cf7-calculation';
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

		$atts['readonly'] = 'readonly';

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$value = (string) reset( $tag->values );

		if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
			$atts['placeholder'] = $value;
			$value               = '';
		}

		$value = $tag->get_default_option( $value );

		$value = wpcf7_get_hangover( $tag->name, $value );

		$scval = do_shortcode( '[' . $value . ']' );
		if ( '[' . $value . ']' !== (string) $scval ) {
			$value = esc_attr( $scval );
		}
		$atts['value'] = 0;

		$atts['type'] = 'text';

		$atts['name'] = $tag->name;

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
			sanitize_html_class( $tag->name ),
			$atts,
			$validation_error
		);

		return $html;
	}

	/**
	 * Display a dialog to generate a "calculate" button in the form
	 *
	 * @param WPCF7_ContactForm $contact_form The CF7 contact form.
	 * @param array             $options      Tag generator options.
	 *
	 * @since 1.0.0
	 */
	public function calculate_button_tag_generator( $contact_form, $options ) {
		$data = array(
			'args' => wp_parse_args( $options, array() ),
			'type' => 'calculate_button',
		);
		$this->load_view( 'tag-generator-calculate-button', $data );
	}

	/**
	 * Process "calculate" button tags
	 *
	 * @param WPCF7_FormTag $tag The form tag to process.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function calculate_button_tag_handler( $tag ) {
		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-calculate_button' );

		$atts = array();

		$atts['class']    = $tag->get_class_option( $class ) . ' cf7-calculate_button';
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

		$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

		if ( empty( $value ) ) {
			$value = __( 'Calculate', 'pvb-cf7-calculator' );
		}

		$atts['type']  = 'button';
		$atts['value'] = $value;

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf( '<input %1$s />', $atts );

		return $html;
	}

	/**
	 * Hook for ajax requests. Sends POST values to the form calculator and outputs the results in JSON format.
	 *
	 * @since 1.0.0
	 */
	public function ajax_calculate() {
		$r     = array(); // Return values will be stored here.
		$input = array();

		if ( ! isset( $_POST['_pvb_ajax_calc_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_key( $_POST['_pvb_ajax_calc_nonce'] ),
				'pvb-ajax-calc-nonce'
			)
		) {
			// Do not process without a valid nonce.
			return;
		}

		foreach ( $_POST as $key => $value ) {
			$input[ sanitize_key( $key ) ] = sanitize_text_field( $value );
		}
		if ( ! empty( $input['pvb_form_id'] ) &&
			is_numeric( $input['pvb_form_id'] )
		) {
			$form = WPCF7_ContactForm::get_instance( $input['pvb_form_id'] );
			if ( false !== $form ) {
				$r = $this->calculate_form( $form, $input );
			}
		}
		wp_send_json( $r );
	}

	/**
	 * Calculate all "calculation" values for a given form
	 *
	 * @param WPCF7_ContactForm $form  The CF7 contact form.
	 * @param string[]          $input Sanitized POST input values.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function calculate_form( $form, $input ) {
		$r    = array(); // Return values will be stored here.
		$tags = $form->scan_form_tags();
		foreach ( $tags as $tag ) {
			if ( empty( $tag['name'] ) ) {
				continue;
			} elseif ( 'calculation' === (string) $tag['basetype'] ) {
				$options                           = $this->extract_options( $tag );
				$r[ sanitize_key( $tag['name'] ) ] = sanitize_text_field(
					$this->calculate_equation(
						$form,
						$tag['values'],
						$input,
						$options
					)
				);
				$input[ $tag['name'] ]             = $r[ $tag['name'] ];
				break;
			}
		}
		return $r;
	}

	/**
	 * Processes POST fields and does the calculations before CF7 gets them
	 *
	 * @since 1.0.0
	 */
	public function intercept_submit() {
		if ( ! isset( $_POST['_pvb_ajax_calc_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_key( $_POST['_pvb_ajax_calc_nonce'] ),
				'pvb-ajax-calc-nonce'
			)
		) {
			// Do not process without a valid nonce.
			return;
		}

		if ( ! empty( $_POST['_wpcf7'] )
			&& empty( $_POST['pvb_form_id'] )
			&& is_numeric( $_POST['_wpcf7'] )
		) {
			$form_id = intval( $_POST['_wpcf7'] );
			$form    = WPCF7_ContactForm::get_instance( $form_id );
			if ( false !== (bool) $form ) {
				$input = array();
				foreach ( $_POST as $key => $value ) {
					$input[ sanitize_key( $key ) ] = sanitize_text_field( $value );
				}
				$r = $this->calculate_form( $form, $input );
				if ( is_array( $r ) && ! empty( $r ) ) {
					foreach ( $r as $key => $value ) {
						$_POST[ sanitize_key( $key ) ] = sanitize_text_field( $value );
					}
				}
			}
		}
	}

	/**
	 * Extracts attributes (such as "min", "max" and "precision") from a CF7 tag
	 *
	 * @param WPCF7_FormTag $tag The form tag to process.
	 * @return array
	 * @since 1.0.0
	 */
	private function extract_options( $tag ) {
		$r = array();
		if ( $tag['options'] ) {
			foreach ( $tag['options'] as $opt ) {
				$pieces = explode( ':', $opt );
				if ( $pieces > 1 ) {
					$r[ sanitize_key( $pieces[0] ) ] = sanitize_text_field( $pieces[1] );
				}
			}
		}
		return $r;
	}

	/**
	 * Calculate the value of a "calculation" field based on user input
	 *
	 * @param WPCF7_ContactForm $form    The CF7 contact form.
	 * @param string|array      $values  Expression(s) to calculate.
	 * @param array             $input   Sanitized POST input values.
	 * @param array             $options Modifier options - "min", "max", and "precision".
	 * @return float
	 * @since 1.0.0
	 */
	private function calculate_equation( $form, $values, $input, $options = array() ) {

		if ( is_array( $values ) ) {
			$values = implode( ' ', $values );
		}

		if ( empty( $values ) || empty( $input ) ) {
			return 0;
		}

		$expression = $values;

		// Order input variables from longest to shortest names.
		$keys = array_keys( $input );

		// Handle checkbox groups with nothing selected.
		$tags = $form->scan_form_tags();
		foreach ( $tags as $tag ) {
			if ( 'checkbox' === (string) $tag['basetype'] ) {
				if ( ! in_array( (string) $tag['name'], $keys, true ) ) {
					$keys[] = $tag['name'];
				}
			}
		}

		usort( $keys, array( $this, 'sort_by_length_helper' ) );

		// Replace input variables in equation.
		foreach ( $keys as $key ) {
			if ( is_array( $input[ $key ] ) ) {
				// Only one value?
				if ( 1 === count( $input[ $key ] ) ) {
					$input[ $key ] = array_shift( $input[ $key ] );
				} else {
					// Sum values.
					$sum = 0;
					foreach ( $input[ $key ] as $value ) {
						if ( is_numeric( $value ) ) {
							$sum += $value;
						}
					}
					$input[ $key ] = $sum;
				}
			}
			if ( preg_match( '/[0-9]{4}\-[0-9]{2}-[0-9]{2}/', $input[ $key ] ) ) {
				// Date variable.
				$days       = intval( strtotime( $input[ $key ] . 'T00:00:00 UTC' ) / 86400 );
				$expression = preg_replace(
					'/(^|[^A-Za-z0-9])' . preg_quote( $key, '/' ) . '($|[^A-Za-z0-9])/',
					'$1###RPLC###$2',
					$expression
				);
				$expression = str_replace( '###RPLC###', $days, $expression );
			} else {
				// Not a date variable.
				if ( is_array( $input[ $key ] ) ) {
					// Sum values (for checkboxes).
					$num = array_sum( $input[ $key ] );
				} elseif ( is_numeric( $input[ $key ] ) ) {
					$num = $input[ $key ];
				} else {
					$num = 0;
				}

				// Handle negative numbers.
				if ( $num < 0 ) {
					$num = ' (' . $num . ') ';
				}
				$expression = preg_replace(
					'/(^|[^A-Za-z0-9])' . preg_quote( $key, '/' ) . '($|[^A-Za-z0-9])/',
					'$1###RPLC###$2',
					$expression
				);
				$expression = str_replace( '###RPLC###', $num, $expression );
			}
		}

		// Fix nested parentheses bug.
		while ( preg_match( '/\(\s*\(/', $expression ) ) {
			$expression = preg_replace( '/\(\s*\(/', '(1*( ', $expression );
		}

		// Calculate.
		$calculator = PVBCalculator::create();
		try {
			$r = $calculator->calculate( $expression );
		} catch ( \Exception $e ) {
			$r = $e->getMessage();
		}

		// Apply minimum.
		if ( isset( $options['min'] ) && is_numeric( $options['min'] ) ) {
			$r = max( $r, $options['min'] );
		}

		// Apply maximum.
		if ( isset( $options['max'] ) && is_numeric( $options['max'] ) ) {
			$r = min( $r, $options['max'] );
		}

		// Apply rounding.
		if ( isset( $options['precision'] ) && is_numeric( $options['precision'] ) ) {
			$r = round( $r, $options['precision'] );
		}

		return $r;
	}

	/**
	 * Check if Contact Form 7 is active and its version is the one required by this plugin (or newer)
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	private function check_cf7() {
		return defined( 'WPCF7_VERSION' ) ? version_compare( WPCF7_VERSION, self::CF7_VERSION_REQUIRED, '>=' ) : false;
	}

	/**
	 * Check if the premium version of this plugin is active
	 *
	 * @return boolean
	 * @since 1.0.9
	 */
	private function check_pro() {
		return class_exists( 'PVBCF7CalculatorPro\lib\PVBCF7Calculator' );
	}

	/**
	 * Load a view
	 *
	 * @param string $view Name of the file in the views subdirectory of the plugin.
	 * @param array  $data Arguments to make available to the view as PHP variables.
	 * @since 1.0.0
	 */
	private function load_view( $view, $data = array() ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$$key = $value;
			}
		}
		$plugin_version = $this->plugin_version;
		include PVB_CF7_CALCULATOR_PATH . '/views/' . $view . '.php';
	}

	/**
	 * Helper function to sort by length.
	 * Returns difference in length for two strings.
	 *
	 * @param string $a First string.
	 * @param string $b Second string.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	private function sort_by_length_helper( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	}

}
