<?php
/**
 * File: class-calculator.php
 *
 * Defines the Calculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Internal;

use PVBCF7Calculator\lib\Math\RoundingMode;
use PVBCF7Calculator\lib\Math\Exception\RoundingNecessaryException;

/**
 * Performs basic operations on arbitrary size integers.
 *
 * All parameters must be validated as non-empty strings of digits,
 * without leading zero, and with an optional leading minus sign if the number is not zero.
 *
 * Any other parameter format will lead to undefined behaviour.
 * All methods must return strings respecting this format.
 *
 * @internal
 */
abstract class Calculator {

	/**
	 * The maximum exponent value allowed for the pow() method.
	 */
	const MAX_POWER = 1000000;

	/**
	 * The Calculator instance in use.
	 *
	 * @var Calculator|null
	 */
	private static $instance;

	/**
	 * Sets the Calculator instance to use.
	 *
	 * An instance is typically set only in unit tests: the autodetect is usually the best option.
	 *
	 * @param Calculator|null $calculator The calculator instance, or NULL to revert to autodetect.
	 *
	 * @return void
	 */
	public static function set( $calculator = null ) {
		self::$instance = $calculator;
	}

	/**
	 * Returns the Calculator instance to use.
	 *
	 * If none has been explicitly set, the fastest available implementation will be returned.
	 *
	 * @return Calculator
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = self::detect();
		}

		return self::$instance;
	}

	/**
	 * Returns the fastest available Calculator implementation.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return Calculator
	 */
	private static function detect() {
		if ( extension_loaded( 'gmp' ) ) {
			return new Calculator\GmpCalculator();
		}

		if ( extension_loaded( 'bcmath' ) ) {
			return new Calculator\BcMathCalculator();
		}

		return new Calculator\NativeCalculator();
	}

	/**
	 * Extracts the digits, sign, and length of the operands.
	 *
	 * @param string $a    The first operand.
	 * @param string $b    The second operand.
	 * @param string $a_dig A variable to store the digits of the first operand.
	 * @param string $b_dig A variable to store the digits of the second operand.
	 * @param bool   $a_neg A variable to store whether the first operand is negative.
	 * @param bool   $b_neg A variable to store whether the second operand is negative.
	 * @param bool   $a_len A variable to store the number of digits in the first operand.
	 * @param bool   $b_len A variable to store the number of digits in the second operand.
	 *
	 * @return void
	 */
	final protected function init( $a, $b, &$a_dig, &$b_dig, &$a_neg, &$b_neg, &$a_len, &$b_len ) {
		$a_neg = ( '-' === $a[0] );
		$b_neg = ( '-' === $b[0] );

		$a_dig = $a_neg ? substr( $a, 1 ) : $a;
		$b_dig = $b_neg ? substr( $b, 1 ) : $b;

		$a_len = strlen( $a_dig );
		$b_len = strlen( $b_dig );
	}

	/**
	 * Returns the absolute value of a number.
	 *
	 * @param string $n The number.
	 *
	 * @return string The absolute value.
	 */
	public function abs( $n ) {
		return ( '-' === $n[0] ) ? substr( $n, 1 ) : $n;
	}

	/**
	 * Negates a number.
	 *
	 * @param string $n The number.
	 *
	 * @return string The negated value.
	 */
	public function neg( $n ) {
		if ( '0' === $n ) {
			return '0';
		}

		if ( '-' === $n[0] ) {
			return substr( $n, 1 );
		}

		return '-' . $n;
	}

	/**
	 * Compares two numbers.
	 *
	 * @param string $a The first number.
	 * @param string $b The second number.
	 *
	 * @return int [-1, 0, 1] If the first number is less than, equal to, or greater than the second number.
	 */
	public function cmp( $a, $b ) {
		$this->init( $a, $b, $a_dig, $b_dig, $a_neg, $b_neg, $a_len, $b_len );

		if ( $a_neg && ! $b_neg ) {
			return -1;
		}

		if ( $b_neg && ! $a_neg ) {
			return 1;
		}

		if ( $a_len < $b_len ) {
			$result = -1;
		} elseif ( $a_len > $b_len ) {
			$result = 1;
		} elseif ( (float) $a_dig === (float) $b_dig ) {
			$result = 0;
		} elseif ( $a_dig < $b_dig ) {
			$result = -1;
		} else {
			$result = 1;
		}

		return $a_neg ? -$result : $result;
	}

	/**
	 * Adds two numbers.
	 *
	 * @param string $a The augend.
	 * @param string $b The addend.
	 *
	 * @return string The sum.
	 */
	abstract public function add( $a, $b);

	/**
	 * Subtracts two numbers.
	 *
	 * @param string $a The minuend.
	 * @param string $b The subtrahend.
	 *
	 * @return string The difference.
	 */
	abstract public function sub( $a, $b);

	/**
	 * Multiplies two numbers.
	 *
	 * @param string $a The multiplicand.
	 * @param string $b The multiplier.
	 *
	 * @return string The product.
	 */
	abstract public function mul( $a, $b);

	/**
	 * Returns the quotient of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string The quotient.
	 */
	abstract public function div_q( $a, $b);

	/**
	 * Returns the remainder of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string The remainder.
	 */
	abstract public function div_r( $a, $b);

	/**
	 * Returns the quotient and remainder of the division of two numbers.
	 *
	 * @param string $a The dividend.
	 * @param string $b The divisor, must not be zero.
	 *
	 * @return string[] An array containing the quotient and remainder.
	 */
	abstract public function div_q_r( $a, $b);

	/**
	 * Exponentiates a number.
	 *
	 * @param string $a The base.
	 * @param int    $e The exponent, validated as an integer between 0 and MAX_POWER.
	 *
	 * @return string The power.
	 */
	abstract public function pow( $a, $e);

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
		if ( '0' === $a ) {
			return $this->abs( $b );
		}

		if ( '0' === $b ) {
			return $this->abs( $a );
		}

		return $this->gcd( $b, $this->div_r( $a, $b ) );
	}

	/**
	 * Performs a rounded division.
	 *
	 * Rounding is performed when the remainder of the division is not zero.
	 *
	 * @param string $a            The dividend.
	 * @param string $b            The divisor.
	 * @param int    $rounding_mode The rounding mode.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException  If the rounding mode is invalid.
	 * @throws RoundingNecessaryException If RoundingMode::UNNECESSARY is provided but rounding is necessary.
	 */
	public function div_round( $a, $b, $rounding_mode ) {
		list($quotient, $remainder) = $this->div_q_r( $a, $b );

		$has_discarded_fraction = ( '0' !== $remainder );
		$is_positive_or_zero    = ( '-' === $a[0] ) === ( '-' === $b[0] );

		$discarded_fraction_sign = function () use ( $remainder, $b ) {
			$r = $this->abs( $this->mul( $remainder, '2' ) );
			$b = $this->abs( $b );

			return $this->cmp( $r, $b );
		};

		$increment = false;

		switch ( $rounding_mode ) {
			case RoundingMode::UNNECESSARY:
				if ( $has_discarded_fraction ) {
					throw RoundingNecessaryException::roundingNecessary();
				}
				break;

			case RoundingMode::UP:
				$increment = $has_discarded_fraction;
				break;

			case RoundingMode::DOWN:
				break;

			case RoundingMode::CEILING:
				$increment = $has_discarded_fraction && $is_positive_or_zero;
				break;

			case RoundingMode::FLOOR:
				$increment = $has_discarded_fraction && ! $is_positive_or_zero;
				break;

			case RoundingMode::HALF_UP:
				$increment = $discarded_fraction_sign() >= 0;
				break;

			case RoundingMode::HALF_DOWN:
				$increment = $discarded_fraction_sign() > 0;
				break;

			case RoundingMode::HALF_CEILING:
				$increment = $is_positive_or_zero ? $discarded_fraction_sign() >= 0 : $discarded_fraction_sign() > 0;
				break;

			case RoundingMode::HALF_FLOOR:
				$increment = $is_positive_or_zero ? $discarded_fraction_sign() > 0 : $discarded_fraction_sign() >= 0;
				break;

			case RoundingMode::HALF_EVEN:
				$last_digit         = (int) substr( $quotient, -1 );
				$last_digit_is_even = ( 0 === $last_digit % 2 );
				$increment          = $last_digit_is_even ?
										$discarded_fraction_sign() > 0 :
										discarded_fraction_sign() >= 0;
				break;

			default:
				throw new \InvalidArgumentException( 'Invalid rounding mode.' );
		}

		if ( $increment ) {
			return $this->add( $quotient, $is_positive_or_zero ? '1' : '-1' );
		}

		return $quotient;
	}
}
