<?php
/**
 * File: class-tokenizer.php
 *
 * Defines the Tokenizer class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

/**
 * Breaks down an expression into tokens.
 */
class Tokenizer implements TokenizerInterface {

	/**
	 * Breaks down an expression into tokens.
	 *
	 * @param string $expression     Expression to tokenize.
	 * @param array  $function_names Names of functions to support.
	 *
	 * @return array Tokens of $expression
	 * @throws \InvalidArgumentException If expression is not valid.
	 */
	public function tokenize( $expression, $function_names = array() ) {
		$expr_length = strlen( $expression );

		$tokens        = array();
		$number_buffer = '';

		for ( $i = 0; $i < $expr_length; $i++ ) {
			if ( Tokens::MINUS === $expression[ $i ]
				&& ( 0 === $i ||
					Tokens::PAREN_LEFT === $expression[ $i - 1 ] ||
					Tokens::POW === $expression[ $i - 1 ] ||
					Tokens::ARG_SEPARATOR === $expression[ $i - 1 ]
				) ) {
				$number_buffer .= $expression[ $i ];
			} elseif ( ctype_digit( $expression[ $i ] ) || Tokens::FLOAT_POINT === $expression[ $i ] ) {
				$number_buffer .= $expression[ $i ];
			} elseif ( ! ctype_digit( $expression[ $i ] ) &&
				Tokens::FLOAT_POINT !== $expression[ $i ] &&
				strlen( $number_buffer ) > 0
			) {
				if ( ! is_numeric( $number_buffer ) ) {
					throw new \InvalidArgumentException( 'Invalid float number detected (more than 1 float point?)' );
				}

				$tokens[]      = $number_buffer;
				$number_buffer = '';
				$i--;
			} elseif ( in_array( $expression[ $i ], Tokens::PARENTHESES, true ) ) {
				if ( $tokens && Tokens::PAREN_LEFT === $expression[ $i ] &&
					( is_numeric( $tokens[ count( $tokens ) - 1 ] ) ||
						in_array( $tokens[ count( $tokens ) - 1 ], Tokens::PARENTHESES, true )
					) ) {
					$tokens[] = Tokens::MULT;
				}

				$tokens[] = $expression[ $i ];
			} elseif ( in_array( $expression[ $i ], Tokens::OPERATORS, true ) ) {
				if ( $i + 1 < $expr_length && Tokens::POW !== $expression[ $i ]
					&& in_array( $expression[ $i + 1 ], Tokens::OPERATORS, true ) ) {
					throw new \InvalidArgumentException( 'Invalid expression' );
				}
				$tokens[] = $expression[ $i ];
			} elseif ( Tokens::ARG_SEPARATOR === $expression[ $i ] ) {
				$tokens[] = $expression[ $i ];
			} elseif ( count( $function_names ) > 0 ) {
				foreach ( $function_names as $function_name ) {
					$name_length = strlen( $function_name );
					if ( $i + $name_length < $expr_length
						&& substr( $expression, $i, $name_length ) === $function_name ) {
						if ( $tokens && is_numeric( $tokens[ count( $tokens ) - 1 ] ) ) {
							$tokens[] = Tokens::MULT;
						}
						$tokens[] = $function_name;
						$i        = $i + $name_length - 1;
					}
				}
			} else {
				throw new \InvalidArgumentException( "Invalid token occurred ({$expression[$i]})" );
			}
		}

		if ( strlen( $number_buffer ) > 0 ) {
			if ( ! is_numeric( $number_buffer ) ) {
				throw new \InvalidArgumentException( 'Invalid float number detected (more than 1 float point?)' );
			}

			$tokens[] = $number_buffer;
		}

		return $tokens;
	}
}
