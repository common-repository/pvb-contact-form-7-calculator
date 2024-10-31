<?php
/**
 * File: class-wordpresssettingsframework.php
 *
 * Defines the WordPressSettingsFramework class.
 *
 * @since 1.0.6
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\wpSettingsFramework;

/**
 * PVB WordPress Settings Framework
 *
 * @author  Gilbert Pellegrom, James Kemp, Petko Bossakov
 * @link    https://github.com/pbosakov/WordPress-Settings-Framework
 * @version 1.0.0
 * @license MIT
 */

/**
 * WordPressSettingsFramework class
 */
class WordPressSettingsFramework {
	/**
	 * Settings wrapper
	 *
	 * @access private
	 * @var array
	 */
	private $settings_wrapper;

	/**
	 * Settings
	 *
	 * @access private
	 * @var array
	 */
	private $settings;

	/**
	 * Tabs
	 *
	 * @access private
	 * @var array
	 */
	private $tabs;

	/**
	 * Option group
	 *
	 * @access private
	 * @var string
	 */
	private $option_group;

	/**
	 * Settings page
	 *
	 * @access private
	 * @var array
	 */
	private $settings_page = array();

	/**
	 * Options path
	 *
	 * @access private
	 * @var string
	 */
	private $options_path;

	/**
	 * Options URL
	 *
	 * @access private
	 * @var string
	 */
	private $options_url;

	/**
	 * Setting defaults
	 *
	 * @access protected
	 * @var array
	 */
	protected $setting_defaults = array(
		'id'          => 'default_field',
		'title'       => 'Default Field',
		'desc'        => '',
		'std'         => '',
		'type'        => 'text',
		'placeholder' => '',
		'choices'     => array(),
		'class'       => '',
		'subfields'   => array(),
	);

	/**
	 * WordPressSettingsFramework constructor.
	 *
	 * @param string $settings_file The file defining the settings.
	 * @param string $option_group  Option group.
	 */
	public function __construct( $settings_file, $option_group = false ) {
		if ( ! is_file( $settings_file ) ) {
			return;
		}

		require_once $settings_file;

		$this->option_group = preg_replace( '/[^a-z0-9]+/i', '', basename( $settings_file, '.php' ) );

		if ( $option_group ) {
			$this->option_group = $option_group;
		}

		$this->options_path = plugin_dir_path( __FILE__ );
		$this->options_url  = plugin_dir_url( __FILE__ );

		$this->construct_settings();

		if ( is_admin() ) {
			global $pagenow;

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabless_settings_sections' ), 10 );

			$whereami = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

			if ( ! empty( $whereami ) && $whereami === $this->settings_page['slug'] ) {
				if ( 'options-general.php' !== $pagenow ) {
					add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				}
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}

			if ( $this->has_tabs() ) {
				add_action( 'wpsf_before_settings_' . $this->option_group, array( $this, 'tab_links' ) );

				remove_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabless_settings_sections' ), 10 );
				add_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabbed_settings_sections' ), 10 );
			}
		}
	}

	/**
	 * Construct Settings.
	 */
	public function construct_settings() {
		$this->settings_wrapper = apply_filters( 'wpsf_register_settings_' . $this->option_group, array() );

		if ( ! is_array( $this->settings_wrapper ) ) {
			return new WP_Error( 'broke', __( 'WPSF settings must be an array' ) );
		}

		// If "sections" is set, this settings group probably has tabs.
		if ( isset( $this->settings_wrapper['sections'] ) ) {
			$this->tabs     = ( isset( $this->settings_wrapper['tabs'] ) ) ? $this->settings_wrapper['tabs'] : array();
			$this->settings = $this->settings_wrapper['sections'];
			// If not, it's probably just an array of settings.
		} else {
			$this->settings = $this->settings_wrapper;
		}

		$this->settings_page['slug'] = sprintf( '%s-settings', str_replace( '_', '-', $this->option_group ) );
	}

	/**
	 * Get the option group for this instance
	 *
	 * @return string the "option_group"
	 */
	public function get_option_group() {
		return $this->option_group;
	}

	/**
	 * Registers the internal WordPress settings
	 */
	public function admin_init() {
		register_setting( $this->option_group, $this->option_group . '_settings', array( $this, 'settings_validate' ) );
		$this->process_settings();
	}

	/**
	 * Add Settings Page
	 *
	 * @param array $args Parameters for the settings page.
	 */
	public function add_settings_page( $args ) {
		$defaults = array(
			'parent_slug' => false,
			'page_slug'   => '',
			'page_title'  => '',
			'menu_title'  => '',
			'capability'  => 'manage_options',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->settings_page['title']      = $args['page_title'];
		$this->settings_page['capability'] = $args['capability'];
		if ( $args['parent_slug'] ) {
			add_submenu_page(
				$args['parent_slug'],
				$this->settings_page['title'],
				$args['menu_title'],
				$args['capability'],
				$this->settings_page['slug'],
				array( $this, 'settings_page_content' ),
				apply_filters( 'wpsf_menu_position_' . $this->option_group, null )
			);
		} else {
			add_menu_page(
				$this->settings_page['title'],
				$args['menu_title'],
				$args['capability'],
				$this->settings_page['slug'],
				array( $this, 'settings_page_content' ),
				apply_filters( 'wpsf_menu_icon_url_' . $this->option_group, '' ),
				apply_filters( 'wpsf_menu_position_' . $this->option_group, null )
			);
		}
	}

	/**
	 * Settings Page Content
	 */
	public function settings_page_content() {
		if ( ! current_user_can( $this->settings_page['capability'] ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php echo esc_html( $this->settings_page['title'] ); ?></h2>
			<?php
			// Output your settings form.
			$this->settings();
			?>
		</div>
		<?php

	}

	/**
	 * Displays any errors from the WordPress settings API
	 */
	public function admin_notices() {
		settings_errors();
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function admin_enqueue_scripts() {
		// Register and enqueue scripts.
		wp_register_script(
			'jquery-ui-timepicker',
			$this->options_url . 'assets/vendor/jquery-timepicker/jquery.ui.timepicker.min.js',
			array( 'jquery', 'jquery-ui-core' ),
			'0.3.3',
			true
		);
		wp_register_script(
			'wpsf',
			$this->options_url . 'assets/js/main.js',
			array( 'jquery' ),
			'1.6.6',
			true
		);

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-timepicker' );
		wp_enqueue_script( 'wpsf' );

		// Register and enqueue styles.
		wp_register_style(
			'jquery-ui-timepicker',
			$this->options_url . 'assets/vendor/jquery-timepicker/jquery.ui.timepicker.css',
			array(),
			'0.3.3'
		);
		wp_register_style(
			'wpsf',
			$this->options_url . 'assets/css/main.css',
			array(),
			'1.6.6'
		);
		wp_register_style(
			'jquery-ui-css',
			$this->options_url . 'assets/css/jquery-ui.css',
			array(),
			'1.8.21'
		);

		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'jquery-ui-timepicker' );
		wp_enqueue_style( 'jquery-ui-css' );
		wp_enqueue_style( 'wpsf' );
	}

	/**
	 * Adds a filter for settings validation.
	 *
	 * @param mixed $input Input to validate.
	 *
	 * @return array
	 */
	public function settings_validate( $input ) {
		return apply_filters( $this->option_group . '_settings_validate', $input );
	}

	/**
	 * Displays the "section_description" if specified in $this->settings
	 *
	 * @param array $args Callback args from add_settings_section().
	 */
	public function section_intro( $args ) {
		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $section ) {
				if ( (string) $section['section_id'] === (string) $args['id'] ) {
					if ( isset( $section['section_description'] ) && $section['section_description'] ) {
						echo '<div class="wpsf-section-description wpsf-section-description--',
							esc_attr( $section['section_id'] ) . '">',
							esc_html( $section['section_description'] ),
							'</div>';
					}
					break;
				}
			}
		}
	}

	/**
	 * Processes $this->settings and adds the sections and fields via the WordPress settings API
	 */
	private function process_settings() {
		if ( ! empty( $this->settings ) ) {
			usort( $this->settings, array( $this, 'sort_array' ) );

			foreach ( $this->settings as $section ) {
				if ( isset( $section['section_id'] ) && $section['section_id'] && isset( $section['section_title'] ) ) {
					$page_name = ( $this->has_tabs() ) ? sprintf( '%s_%s', $this->option_group, $section['tab_id'] ) : $this->option_group;

					add_settings_section( $section['section_id'], $section['section_title'], array( $this, 'section_intro' ), $page_name );

					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) && ! empty( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( isset( $field['id'] ) && $field['id'] && isset( $field['title'] ) ) {
								$title = ! empty( $field['subtitle'] ) ? sprintf( '%s <span class="wpsf-subtitle">%s</span>', $field['title'], $field['subtitle'] ) : $field['title'];

								add_settings_field(
									$field['id'],
									$title,
									array( $this, 'generate_setting' ),
									$page_name,
									$section['section_id'],
									array(
										'section' => $section,
										'field'   => $field,
									)
								);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Usort callback. Sorts $this->settings by "section_order"
	 *
	 * @param mixed $a First element to compare.
	 * @param mixed $b Second element to compare.
	 *
	 * @return bool
	 */
	public function sort_array( $a, $b ) {
		if ( ! isset( $a['section_order'] ) ) {
			return false;
		}

		return $a['section_order'] > $b['section_order'];
	}

	/**
	 * Generates the HTML output of the settings fields
	 *
	 * @param array $args Callback args from add_settings_field().
	 */
	public function generate_setting( $args ) {
		$section                = $args['section'];
		$this->setting_defaults = apply_filters( 'wpsf_defaults_' . $this->option_group, $this->setting_defaults );

		$args = wp_parse_args( $args['field'], $this->setting_defaults );

		$options = get_option( $this->option_group . '_settings' );

		$args['id']    = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'], $args['id'] ) : sprintf( '%s_%s', $section['section_id'], $args['id'] );
		$args['value'] = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$args['name']  = $this->generate_field_name( $args['id'] );

		do_action( 'wpsf_before_field_' . $this->option_group );
		do_action( 'wpsf_before_field_' . $this->option_group . '_' . $args['id'] );

		$this->do_field_method( $args );

		do_action( 'wpsf_after_field_' . $this->option_group );
		do_action( 'wpsf_after_field_' . $this->option_group . '_' . $args['id'] );
	}

	/**
	 * Do field method, if it exists
	 *
	 * @param array $args Field method parameters.
	 */
	public function do_field_method( $args ) {
		$generate_field_method = sprintf( 'generate_%s_field', $args['type'] );

		if ( method_exists( $this, $generate_field_method ) ) {
			$this->$generate_field_method( $args );
		}
	}

	/**
	 * Generate: Text field
	 *
	 * @param array $args Text field parameters.
	 */
	public function generate_text_field( $args ) {
		echo '<input type="text" name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="' . esc_attr( stripslashes( $args['value'] ) ) .
			'" placeholder="' . esc_attr( $args['placeholder'] ) .
			'" class="regular-text ' . esc_attr( $args['class'] ) . '" />';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Number field
	 *
	 * @param array $args Number field parameters.
	 */
	public function generate_number_field( $args ) {
		echo '<input type="number" name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="' . esc_attr( stripslashes( $args['value'] ) ) .
			'" placeholder="' . esc_attr( $args['placeholder'] ) .
			'" class="regular-text ' . esc_attr( $args['class'] ) . '" />';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Time field
	 *
	 * @param array $args Time field parameters.
	 */
	public function generate_time_field( $args ) {

		$timepicker = ! empty( $args['timepicker'] ) ? wp_json_encode( $args['timepicker'] ) : '';

		echo '<input name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="' . esc_attr( $args['value'] ) .
			'" class="timepicker regular-text ' . esc_attr( $args['class'] ) .
			'" data-timepicker="' . esc_attr( $timepicker ) . '" />';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Date field
	 *
	 * @param array $args Date field parameters.
	 */
	public function generate_date_field( $args ) {
		$datepicker = ! empty( $args['datepicker'] ) ? wp_json_encode( $args['datepicker'] ) : '';

		echo '<input name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="' . esc_attr( stripslashes( $args['value'] ) ) .
			'" class="datepicker regular-text ' . esc_attr( $args['class'] ) .
			'" data-datepicker="' . esc_attr( $datepicker ) . '" />';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Group field
	 *
	 * Generates a table of subfields, and a javascript template for create new repeatable rows
	 *
	 * @param array $args Group field parameters.
	 */
	public function generate_group_field( $args ) {
		$row_count = count( $args['value'] );

		echo '<table class="widefat wpsf-group" cellspacing="0">';

		echo '<tbody>';

		for ( $row = 0; $row < $row_count; $row ++ ) {
			echo wp_kses_post( $this->generate_group_row_template( $args, false, $row ) );
		}

		echo '</tbody>';

		echo '</table>';

		printf(
			'<script type="text/html" id="%s_template">%s</script>',
			esc_attr( $args['id'] ),
			wp_kses_post( $this->generate_group_row_template( $args, true ) )
		);

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate group row template
	 *
	 * @param array $args  Field arguments.
	 * @param bool  $blank Blank values.
	 * @param int   $row   Iterator.
	 *
	 * @return string|bool
	 */
	public function generate_group_row_template( $args, $blank = false, $row = 0 ) {
		$row_template = false;

		if ( $args['subfields'] ) {
			$row_class = ( 0 === $row % 2 ) ? 'alternate' : '';

			$row_template .= sprintf( '<tr class="wpsf-group__row %s">', $row_class );

			$row_template .= sprintf( '<td class="wpsf-group__row-index"><span>%d</span></td>', $row );

			$row_template .= '<td class="wpsf-group__row-fields">';

			foreach ( $args['subfields'] as $subfield ) {
				$subfield = wp_parse_args( $subfield, $this->setting_defaults );

				$subfield['value'] = ( $blank ) ?
					'' :
					( isset( $args['value'][ $row ][ $subfield['id'] ] ) ? $args['value'][ $row ][ $subfield['id'] ] : '' );
				$subfield['name']  = sprintf( '%s[%d][%s]', $args['name'], $row, $subfield['id'] );
				$subfield['id']    = sprintf( '%s_%d_%s', $args['id'], $row, $subfield['id'] );

				$row_template .= '<div class="wpsf-group__field-wrapper">';

				$row_template .= sprintf( '<label for="%s" class="wpsf-group__field-label">%s</label>', $subfield['id'], $subfield['title'] );

				ob_start();
				$this->do_field_method( $subfield );
				$row_template .= ob_get_clean();

				$row_template .= '</div>';
			}

			$row_template .= '</td>';

			$row_template .= '<td class="wpsf-group__row-actions">';

			$row_template .= sprintf( '<a href="javascript: void(0);" class="wpsf-group__row-add" data-template="%s_template"><span class="dashicons dashicons-plus-alt"></span></a>', $args['id'] );
			$row_template .= '<a href="javascript: void(0);" class="wpsf-group__row-remove"><span class="dashicons dashicons-trash"></span></a>';

			$row_template .= '</td>';

			$row_template .= '</tr>';
		}

		return $row_template;
	}

	/**
	 * Generate: Select field
	 *
	 * @param array $args Select field parameters.
	 */
	public function generate_select_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		echo '<select name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" class="' . esc_attr( $args['class'] ) . '">';

		foreach ( $args['choices'] as $value => $text ) {
			$selected = ( $value === $args['value'] ) ? 'selected="selected"' : '';

			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				esc_attr( $selected ),
				esc_html( $text )
			);
		}

		echo '</select>';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Password field
	 *
	 * @param array $args Password field parameters.
	 */
	public function generate_password_field( $args ) {
		$args['value'] = esc_attr( stripslashes( $args['value'] ) );

		echo '<input type="password" name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="' . esc_attr( $args['value'] ) .
			'" placeholder="' . esc_attr( $args['placeholder'] ) .
			'" class="regular-text ' . esc_attr( $args['class'] ) . '" />';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Textarea field
	 *
	 * @param array $args Textarea field parameters.
	 */
	public function generate_textarea_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		echo '<textarea name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" placeholder="' . esc_attr( $args['placeholder'] ) .
			'" rows="5" cols="60" class="' . esc_attr( $args['class'] ) .
			'">' . esc_html( $args['value'] ) . '</textarea>';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Radio field
	 *
	 * @param array $args Radio field parameters.
	 */
	public function generate_radio_field( $args ) {
		$args['value'] = esc_html( esc_attr( $args['value'] ) );

		foreach ( $args['choices'] as $value => $text ) {
			$field_id = sprintf( '%s_%s', $args['id'], $value );
			$checked  = (string) $value === (string) $args['value'] ? 'checked="checked"' : '';

			echo sprintf(
				'<label><input type="radio" name="%s" id="%s" value="%s" class="%s" %s> %s</label><br />',
				esc_attr( $args['name'] ),
				esc_attr( $field_id ),
				esc_attr( $value ),
				esc_attr( $args['class'] ),
				esc_attr( $checked ),
				esc_html( $text )
			);
		}

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Checkbox field
	 *
	 * @param array $args Checkbox field parameters.
	 */
	public function generate_checkbox_field( $args ) {
		$args['value'] = esc_attr( stripslashes( $args['value'] ) );
		$checked       = $args['value'] ? 'checked="checked"' : '';

		echo '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" value="0" />';
		echo '<label><input type="checkbox" name="' . esc_attr( $args['name'] ) .
			'" id="' . esc_attr( $args['id'] ) .
			'" value="1" class="' . esc_attr( $args['class'] ) .
			'" ' . esc_attr( $checked ) . '> ' .
			esc_html( $args['desc'] ) .
			'</label>';
	}

	/**
	 * Generate: Checkboxes field
	 *
	 * @param array $args Checkbox field parameters.
	 */
	public function generate_checkboxes_field( $args ) {
		echo '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" value="0" />';

		echo '<ul class="wpsf-list wpsf-list--checkboxes">';
		foreach ( $args['choices'] as $value => $text ) {
			$checked  = ( is_array( $args['value'] ) &&
				in_array( $value, $args['value'], true )
			) ? 'checked="checked"' : '';
			$field_id = sprintf( '%s_%s', $args['id'], $value );

			echo sprintf(
				'<li><label><input type="checkbox" name="%s[]" id="%s" value="%s" class="%s" %s> %s</label></li>',
				esc_attr( $args['name'] ),
				esc_attr( $field_id ),
				esc_attr( $value ),
				esc_attr( $args['class'] ),
				esc_attr( $checked ),
				esc_attr( $text )
			);
		}
		echo '</ul>';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Color field
	 *
	 * @param array $args Color field parameters.
	 */
	public function generate_color_field( $args ) {
		$color_picker_id = sprintf( '%s_cp', $args['id'] );
		$args['value']   = esc_attr( stripslashes( $args['value'] ) );

		echo '<div style="position:relative;">';

		echo sprintf(
			'<input type="text" name="%s" id="%s" value="%s" class="%s">',
			esc_attr( $args['name'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['value'] ),
			esc_attr( $args['class'] )
		);

		echo sprintf(
			'<div id="%s" style="position:absolute;top:0;left:190px;background:#fff;z-index:9999;"></div>',
			esc_attr( $color_picker_id )
		);

		$this->generate_description( $args['desc'] );

		echo '<script type="text/javascript">
            jQuery(document).ready(function($){
                var colorPicker = $("#' . esc_attr( $color_picker_id ) . '");
                colorPicker.farbtastic("#' . esc_attr( $args['id'] ) . '");
                colorPicker.hide();
                $("#' . esc_attr( $args['id'] ) . '").live("focus", function(){
                    colorPicker.show();
                });
                $("#' . esc_attr( $args['id'] ) . '").live("blur", function(){
                    colorPicker.hide();
                    if($(this).val() == "") $(this).val("#");
                });
            });
            </script>';

		echo '</div>';
	}

	/**
	 * Generate: File field
	 *
	 * @param array $args File field parameters.
	 */
	public function generate_file_field( $args ) {
		$args['value'] = esc_attr( $args['value'] );
		$button_id     = sprintf( '%s_button', $args['id'] );

		echo sprintf(
			'<input type="text" name="%s" id="%s" value="%s" class="regular-text %s"> ',
			esc_attr( $args['name'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['value'] ),
			esc_attr( $args['class'] )
		);

		echo sprintf(
			'<input type="button" class="button wpsf-browse" id="%s" value="Browse" />',
			esc_attr( $button_id )
		);

		echo '<script type="text/javascript">
            jQuery(document).ready(function($){
                $("#' . esc_attr( $button_id ) . '").click(function() {

                    tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");

                    window.original_send_to_editor = window.send_to_editor;

                    window.send_to_editor = function(html) {
                        var imgurl = $("img",html).attr("src");
                        $("#' . esc_attr( $args['id'] ) . '").val(imgurl);
                        tb_remove();
                        window.send_to_editor = window.original_send_to_editor;
                    };

                    return false;

                });
            });
        </script>';
	}

	/**
	 * Generate: Editor field
	 *
	 * @param array $args Editor field parameters.
	 */
	public function generate_editor_field( $args ) {
		wp_editor( $args['value'], $args['id'], array( 'textarea_name' => $args['name'] ) );

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Custom field
	 *
	 * @param array $args Custom field parameters.
	 */
	public function generate_custom_field( $args ) {
		echo wp_kses( $args['default'], $this->expanded_alowed_tags() );
	}

	/**
	 * Generate: Multi Inputs field
	 *
	 * @param array $args Multi Inputs field parameters.
	 */
	public function generate_multiinputs_field( $args ) {
		$field_titles = array_keys( $args['default'] );
		$values       = array_values( $args['value'] );

		echo '<div class="wpsf-multifields">';

		$i            = 0;
		$values_count = count( $values );
		while ( $i < $values_count ) :

			$field_id = sprintf( '%s_%s', $args['id'], $i );
			$value    = esc_attr( stripslashes( $values[ $i ] ) );

			echo '<div class="wpsf-multifields__field">';
			echo '<input type="text" name="' .
				esc_attr( $args['name'] ) . '[]" id="' .
				esc_attr( $field_id ) . '" value="' .
				esc_attr( $value ) . '" class="regular-text ' .
				esc_attr( $args['class'] ) . '" placeholder="' .
				esc_attr( $args['placeholder'] ) . '" />';
			echo '<br><span>' . esc_html( $field_titles[ $i ] ) . '</span>';
			echo '</div>';

			$i ++;
		endwhile;

		echo '</div>';

		$this->generate_description( $args['desc'] );
	}

	/**
	 * Generate: Field ID
	 *
	 * @param mixed $id Setting ID.
	 *
	 * @return string
	 */
	public function generate_field_name( $id ) {
		return sprintf( '%s_settings[%s]', $this->option_group, $id );
	}

	/**
	 * Generate: Description
	 *
	 * @param mixed $description Description text.
	 */
	public function generate_description( $description ) {
		if ( $description && '' !== $description ) {
			echo '<p class="description">' . esc_html( $description ) . '</p>';
		}
	}

	/**
	 * Output the settings form
	 */
	public function settings() {
		do_action( 'wpsf_before_settings_' . $this->option_group );
		?>
		<form action="options.php" method="post" novalidate>
			<?php do_action( 'wpsf_before_settings_fields_' . $this->option_group ); ?>
			<?php settings_fields( $this->option_group ); ?>

			<?php do_action( 'wpsf_do_settings_sections_' . $this->option_group ); ?>

			<?php if ( apply_filters( 'wpsf_show_save_changes_button_' . $this->option_group, true ) ) : ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				</p>
			<?php endif; ?>
		</form>
		<?php
		do_action( 'wpsf_after_settings_' . $this->option_group );
	}

	/**
	 * Helper: Get Settings
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings_name = $this->option_group . '_settings';

		static $settings = array();

		if ( isset( $settings[ $settings_name ] ) ) {
			return $settings[ $settings_name ];
		}

		$saved_settings             = get_option( $this->option_group . '_settings' );
		$settings[ $settings_name ] = array();

		foreach ( $this->settings as $section ) {
			if ( empty( $section['fields'] ) ) {
				continue;
			}
			foreach ( $section['fields'] as $field ) {
				if ( ! empty( $field['default'] ) && is_array( $field['default'] ) ) {
					$field['default'] = array_values( $field['default'] );
				}

				$setting_key = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'], $field['id'] ) : sprintf( '%s_%s', $section['section_id'], $field['id'] );

				if ( isset( $saved_settings[ $setting_key ] ) ) {
					$settings[ $settings_name ][ $setting_key ] = $saved_settings[ $setting_key ];
				} else {
					$settings[ $settings_name ][ $setting_key ] = ( isset( $field['default'] ) ) ? $field['default'] : false;
				}
			}
		}

		return $settings[ $settings_name ];
	}

	/**
	 * Tabless Settings sections
	 */
	public function do_tabless_settings_sections() {
		?>
		<div class="wpsf-section wpsf-tabless">
			<?php do_settings_sections( $this->option_group ); ?>
		</div>
		<?php
	}

	/**
	 * Tabbed Settings sections
	 */
	public function do_tabbed_settings_sections() {
		$i = 0;
		foreach ( $this->tabs as $tab_data ) {
			?>
			<div
				id="tab-<?php echo esc_attr( $tab_data['id'] ); ?>"
				class="wpsf-section wpsf-tab wpsf-tab--<?php echo esc_attr( $tab_data['id'] ); ?>
				<?php
				if ( 0 === $i ) {
					echo 'wpsf-tab--active';
				}
				?>
			">
				<div class="postbox">
					<?php do_settings_sections( sprintf( '%s_%s', $this->option_group, $tab_data['id'] ) ); ?>
				</div>
			</div>
			<?php
			$i ++;
		}
	}

	/**
	 * Output the tab links
	 */
	public function tab_links() {
		if ( ! apply_filters( 'wpsf_show_tab_links_' . $this->option_group, true ) ) {
			return;
		}
		do_action( 'wpsf_before_tab_links_' . $this->option_group );
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			$i = 0;
			foreach ( $this->tabs as $tab_data ) {
				$active = ( 0 === $i ) ? 'nav-tab-active' : '';
				?>
				<a
					class="nav-tab wpsf-tab-link <?php echo esc_attr( $active ); ?>"
					href="#tab-<?php echo esc_attr( $tab_data['id'] ); ?>">
					<?php echo esc_html( $tab_data['title'] ); ?>
				</a>
				<?php
				$i ++;
			}
			?>
		</h2>
		<?php
		do_action( 'wpsf_after_tab_links_' . $this->option_group );
	}

	/**
	 * Check if this settings instance has tabs
	 */
	public function has_tabs() {
		if ( ! empty( $this->tabs ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns list of allowed tags for the custom field.
	 */
	private function expanded_alowed_tags() {
		$my_allowed = wp_kses_allowed_html( 'post' );

		// Iframe.
		$my_allowed['iframe'] = array(
			'src'             => array(),
			'height'          => array(),
			'width'           => array(),
			'frameborder'     => array(),
			'allowfullscreen' => array(),
		);

		// Form fields - input.
		$my_allowed['input'] = array(
			'class' => array(),
			'id'    => array(),
			'name'  => array(),
			'value' => array(),
			'type'  => array(),
		);

		// Select.
		$my_allowed['select'] = array(
			'class' => array(),
			'id'    => array(),
			'name'  => array(),
			'value' => array(),
			'type'  => array(),
		);

		// Select options.
		$my_allowed['option'] = array(
			'selected' => array(),
		);

		// Style.
		$my_allowed['style'] = array(
			'types' => array(),
		);

		// Script.
		$my_allowed['script'] = array(
			'type' => array(),
			'src'  => array(),
		);

		return $my_allowed;
	}
}

/**
 * Get a setting from an option group
 *
 * @param string $option_group The option group.
 * @param string $section_id   May also be prefixed with tab ID.
 * @param string $field_id     The field ID.
 *
 * @return mixed
 */
function wpsf_get_setting( $option_group, $section_id, $field_id ) {
	$options = get_option( $option_group . '_settings' );
	if ( isset( $options[ $section_id . '_' . $field_id ] ) ) {
		return $options[ $section_id . '_' . $field_id ];
	}

	return false;
}

/**
 * Delete all the saved settings from a settings file/option group
 *
 * @param string $option_group The option group.
 */
function wpsf_delete_settings( $option_group ) {
	delete_option( $option_group . '_settings' );
}

/**
 * Programmatically update a setting managed by
 * the WordPressSettingsFramework class
 *
 * @param string $option_group The option group.
 * @param string $section_id   May also be prefixed with tab ID.
 * @param string $field_id     The field ID.
 * @param mixed  $value        Setting value.
 */
function wpsf_set_setting(
	$option_group,
	$section_id,
	$field_id,
	$value
) {
	$options                                  = get_option( $option_group . '_settings' );
	$options[ $section_id . '_' . $field_id ] = $value;
	update_option( $option_group . '_settings', $options );
}
