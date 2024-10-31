<?php
/**
 * File: autoload.php
 *
 * Defines the pvb_cf7_calculator_autoload function.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin by looking at the $class_name parameter being passed as an argument.
 *
 * The argument should be in the form: PVBCF7Calculator\Namespace. The
 * function will then break the fully-qualified class name into its pieces and
 * will then build a file to the path based on the namespace.
 *
 * The namespaces in this plugin map to the paths in the directory structure.
 *
 * @param string $class_name The fully-qualified name of the class to load.
 */
function pvb_cf7_calculator_autoload( $class_name ) {
	// If the specified $class_name does not include our namespace, duck out.
	if ( false === strpos( $class_name, 'PVBCF7Calculator' ) ) {
		return;
	}

	$class_name = str_replace(
		'PVBCF7Calculator\lib\PVBCF7Calculator\lib',
		'PVBCF7Calculator\lib',
		$class_name
	);

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace = '';
	for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
		// Read the current component of the file part.
		$current = $file_parts[ $i ];

		// If we're at the first entry, then we're at the filename.
		if ( count( $file_parts ) - 1 === $i ) {
			$file_name = 'class-' . strtolower( $current ) . '.php';
		} else {
			$namespace = '/' . $current . $namespace;
		}
	}

	// Now build a path to the file using mapping to the file location.
	$filepath  = trailingslashit( dirname( __FILE__ ) . $namespace );
	$filepath .= $file_name;

	// If the file exists in the specified path, then include it.
	if ( file_exists( $filepath ) ) {
		include_once $filepath;
	} else {
		wp_die(
			esc_html( "The file attempting to be loaded at $filepath does not exist." )
		);
	}
}
