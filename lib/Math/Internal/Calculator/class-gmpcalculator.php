<?php
/**
 * File: class-gmpcalculator.php
 *
 * Defines the GmpCalculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Internal\Calculator;

use PVBCF7Calculator\lib\Math\Internal\Calculator;

/**
 * Calculator implementation built around the GMP library.
 *
 * @internal
 */
class GmpCalculator extends Calculator {

	/**
	 * Adds two numbers.
	 *
	 * @param string $a The augend.
	 * @param string $b The addend.
	 *
	 * @return string The sum.
	 */
	public function add( $a, $b ) {
		return gmp_strval( gmp_add( $a, $b ) );
	}

	/**
	 * Subtracts two numbers.
	 *
	 * @param string $a The minuend.
	 * @param string $b The subtrahend.
	 *
	 * @return string The difference.
	 */
	public function sub( $a, $b ) {
		return gmp_strval( gmp_sub( $a, $b ) );
	}

	/**
	 * Multiplies two numbers.
	 *
	 * @param string $a The multiplicand.
	 * @param string $b The multiplier.
	 *
	 * @return string The product.
	 */
	public function mul( $a, $b ) {
		return gmp_strval( gmp_mul( $a, $b ) );
	}

	/**
	 * Returns the quotient of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string The quotient.
	 */
	public function div_q( $a, $b ) {
		return gmp_strval( gmp_div_q( $a, $b ) );
	}

	/**
	 * Returns the remainder of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string The remainder.
	 */
	public function div_r( $a, $b ) {
		return gmp_strval( gmp_div_r( $a, $b ) );
	}

	/**
	 * Returns the quotient and remainder of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string[] An array containing the quotient and remainder.
	 */
	public function div_q_r( $a, $b ) {
		list($q, $r) = gmp_div_qr( $a, $b );

		return array(
			gmp_strval( $q ),
			gmp_strval( $r ),
		);
	}

	/**
	 * Exponentiates a number.
	 *
	 * @param string $a The base.
	 * @param int    $e The exponent, validated as an integer between 0 and MAX_POWER.
	 *
	 * @return string The power.
	 */
	public function pow( $a, $e ) {
		return gmp_strval( gmp_pow( $a, $e ) );
	}

	/**
	 * Returns the greatest common divisor of the two numbers.
	 *
	 * This method can be overridden by the concrete implementation if the underlying library
	 * has built-in support for GCD calculations.
	 *
	 * @param string $a The first number.
	 * @param string $b The second number.
	 *
	 * @return string The GCD, always positive, or zero if both arguments are zero.
	 */
	public function gcd( $a, $b ) {
		return gmp_strval( gmp_gcd( $a, $b ) );
	}
}
