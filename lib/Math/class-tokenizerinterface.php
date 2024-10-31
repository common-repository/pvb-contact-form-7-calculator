<?php
/**
 * File: class-tokenizerinterface.php
 *
 * Defines the TokenizerInterface interface.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

interface TokenizerInterface {

	/**
	 * Breaks down an expression into tokens.
	 *
	 * @param string $expression     Expression to tokenize.
	 * @param array  $function_names Names of functions to support.
	 *
	 * @return array Tokens of $expression
	 */
	public function tokenize( $expression, $function_names = array());
}
