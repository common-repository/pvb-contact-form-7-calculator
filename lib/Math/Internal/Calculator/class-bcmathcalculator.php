<?php
/**
 * File: class-bcmathcalculator.php
 *
 * Defines the BcMathCalculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Internal\Calculator;

use PVBCF7Calculator\lib\Math\Internal\Calculator;

/**
 * Calculator implementation built around the bcmath library.
 *
 * @internal
 */
class BcMathCalculator extends Calculator {

	/**
	 * Adds two numbers.
	 *
	 * @param string $a The augend.
	 * @param string $b The addend.
	 *
	 * @return string The sum.
	 */
	public function add( $a, $b ) {
		return bcadd( $a, $b, 0 );
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
		return bcsub( $a, $b, 0 );
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
		return bcmul( $a, $b, 0 );
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
		return bcdiv( $a, $b, 0 );
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
		return bcmod( $a, $b );
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
		$q = bcdiv( $a, $b, 0 );
		$r = bcmod( $a, $b );

		return array( $q, $r );
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
		return bcpow( $a, (string) $e, 0 );
	}
}
