<?php
/**
 * File: class-biginteger.php
 *
 * Defines the BigInteger class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

use PVBCF7Calculator\lib\Math\Exception\DivisionByZeroException;
use PVBCF7Calculator\lib\Math\Exception\IntegerOverflowException;
use PVBCF7Calculator\lib\Math\Exception\MathException;
use PVBCF7Calculator\lib\Math\Internal\Calculator;

/**
 * An arbitrary-size integer.
 *
 * All methods accepting a number as a parameter accept either a BigInteger instance,
 * an integer, or a string representing an arbitrary size integer.
 */
final class BigInteger extends BigNumber {

	/**
	 * The value, as a string of digits with optional leading minus sign.
	 *
	 * No leading zeros must be present.
	 * No leading minus sign must be present if the number is zero.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * Protected constructor. Use a factory method to obtain an instance.
	 *
	 * @param string $value A string of digits, with optional leading minus sign.
	 */
	protected function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * Creates a BigInteger of the given value.
	 *
	 * @param BigNumber|number|string $value Value to convert.
	 *
	 * @return BigInteger
	 *
	 * @throws MathException If the value cannot be converted to a BigInteger.
	 */
	public static function of( $value ) {
		return parent::of( $value )->toBigInteger();
	}

	/**
	 * Parses a string containing an integer in an arbitrary base.
	 *
	 * The string can optionally be prefixed with the `+` or `-` sign.
	 *
	 * @param string $number The number to parse.
	 * @param int    $base   The base of the number, between 2 and 36.
	 *
	 * @return BigInteger
	 *
	 * @throws \InvalidArgumentException If the number is invalid or the base is out of range.
	 */
	public static function parse( $number, $base = 10 ) {
		$dictionary = '0123456789abcdefghijklmnopqrstuvwxyz';

		if ( '' === $number ) {
			throw new \InvalidArgumentException( 'The value cannot be empty.' );
		}

		if ( $base < 2 || $base > 36 ) {
			throw new \InvalidArgumentException( sprintf( 'Base %d is not in range 2 to 36.', $base ) );
		}

		if ( '-' === $number[0] ) {
			$sign   = '-';
			$number = substr( $number, 1 );
		} elseif ( '+' === $number[0] ) {
			$sign   = '';
			$number = substr( $number, 1 );
		} else {
			$sign = '';
		}

		if ( '' === $number ) {
			throw new \InvalidArgumentException( 'The value cannot be empty.' );
		}

		$number = ltrim( $number, '0' );

		if ( '' === $number ) {
			// The result will be the same in any base, avoid further calculation.
			return self::zero();
		}

		if ( '1' === $number ) {
			// The result will be the same in any base, avoid further calculation.
			return new BigInteger( $sign . '1' );
		}

		if ( 10 === $base && ctype_digit( $number ) ) {
			// The number is usable as is, avoid further calculation.
			return new BigInteger( $sign . $number );
		}

		$calc   = Calculator::get();
		$number = strtolower( $number );

		$result = '0';
		$power  = '1';

		for ( $i = strlen( $number ) - 1; $i >= 0; $i-- ) {
			$char  = $number[ $i ];
			$index = strpos( $dictionary, $char );

			if ( false === $index || $index >= $base ) {
				throw new \InvalidArgumentException( sprintf( '"%s" is not a valid character in base %d.', $char, $base ) );
			}

			if ( 0 !== $index ) {
				$add    = ( 1 === $index ) ? $power : $calc->mul( $power, (string) $index );
				$result = $calc->add( $result, $add );
			}

			$power = $calc->mul( $power, (string) $base );
		}

		return new BigInteger( $sign . $result );
	}

	/**
	 * Returns a BigInteger representing zero.
	 *
	 * @return BigInteger
	 */
	public static function zero() {
		static $zero;

		if ( null === $zero ) {
			$zero = new BigInteger( '0' );
		}

		return $zero;
	}

	/**
	 * Returns a BigInteger representing one.
	 *
	 * @return BigInteger
	 */
	public static function one() {
		static $one;

		if ( null === $one ) {
			$one = new BigInteger( '1' );
		}

		return $one;
	}

	/**
	 * Returns a BigInteger representing ten.
	 *
	 * @return BigInteger
	 */
	public static function ten() {
		static $ten;

		if ( null === $ten ) {
			$ten = new BigInteger( '10' );
		}

		return $ten;
	}

	/**
	 * Returns the sum of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The number to add. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger The result.
	 *
	 * @throws MathException If the number is not valid, or is not convertible to a BigInteger.
	 */
	public function plus( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value ) {
			return $this;
		}

		$value = Calculator::get()->add( $this->value, $that->value );

		return new BigInteger( $value );
	}

	/**
	 * Returns the difference of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The number to subtract. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger The result.
	 *
	 * @throws MathException If the number is not valid, or is not convertible to a BigInteger.
	 */
	public function minus( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value ) {
			return $this;
		}

		$value = Calculator::get()->sub( $this->value, $that->value );

		return new BigInteger( $value );
	}

	/**
	 * Returns the product of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The multiplier. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger The result.
	 *
	 * @throws MathException If the multiplier is not a valid number, or is not convertible to a BigInteger.
	 */
	public function multipliedBy( $that ) {
		$that = self::of( $that );

		if ( '1' === $that->value ) {
			return $this;
		}

		$value = Calculator::get()->mul( $this->value, $that->value );

		return new BigInteger( $value );
	}

	/**
	 * Returns the result of the division of this number by the given one.
	 *
	 * @param BigNumber|number|string $that          The divisor. Must be convertible to a BigInteger.
	 * @param int                     $rounding_mode An optional rounding mode.
	 *
	 * @return BigInteger The result.
	 *
	 * @throws DivisionByZeroException If the divisor is not a valid number, is not convertible to a BigInteger, is zero,
	 *                                 or RoundingMode::UNNECESSARY is used and the remainder is not zero.
	 */
	public function dividedBy( $that, int $rounding_mode = RoundingMode::UNNECESSARY ) {
		$that = self::of( $that );

		if ( '1' === $that->value ) {
			return $this;
		}

		if ( '0' === $that->value ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$result = Calculator::get()->div_round( $this->value, $that->value, $rounding_mode );

		return new BigInteger( $result );
	}

	/**
	 * Returns this number exponentiated to the given value.
	 *
	 * @param int $exponent The exponent.
	 *
	 * @return BigInteger The result.
	 *
	 * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
	 */
	public function power( $exponent ) {
		if ( 0 === $exponent ) {
			return self::one();
		}

		if ( 1 === $exponent ) {
			return $this;
		}

		if ( $exponent < 0 || $exponent > Calculator::MAX_POWER ) {
			throw new \InvalidArgumentException(
				sprintf(
					'The exponent %d is not in the range 0 to %d.',
					$exponent,
					Calculator::MAX_POWER
				)
			);
		}

		return new BigInteger( Calculator::get()->pow( $this->value, $exponent ) );
	}

	/**
	 * Returns the quotient of the division of this number by the given one.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger
	 *
	 * @throws DivisionByZeroException If the divisor is zero.
	 */
	public function quotient( $that ) {
		$that = self::of( $that );

		if ( '1' === $that->value ) {
			return $this;
		}

		if ( '0' === $that->value ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$quotient = Calculator::get()->div_q( $this->value, $that->value );

		return new BigInteger( $quotient );
	}

	/**
	 * Returns the remainder of the division of this number by the given one.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger
	 *
	 * @throws DivisionByZeroException If the divisor is zero.
	 */
	public function remainder( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$remainder = Calculator::get()->div_r( $this->value, $that->value );

		return new BigInteger( $remainder );
	}

	/**
	 * Returns the quotient and remainder of the division of this number by the given one.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigInteger.
	 *
	 * @return BigInteger[] An array containing the quotient and the remainder.
	 *
	 * @throws DivisionByZeroException If the divisor is zero.
	 */
	public function quotientAndRemainder( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$r         = Calculator::get()->div_q_r( $this->value, $that->value );
		$quotient  = $r[0];
		$remainder = $r[1];

		return array(
			new BigInteger( $quotient ),
			new BigInteger( $remainder ),
		);
	}

	/**
	 * Returns the greatest common divisor of this number and the given one.
	 *
	 * The GCD is always positive, unless both operands are zero, in which case it is zero.
	 *
	 * @param BigNumber|number|string $that The operand. Must be convertible to an integer number.
	 *
	 * @return BigInteger
	 */
	public function gcd( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value && '-' !== $this->value[0] ) {
			return $this;
		}

		if ( '0' === $this->value && '-' !== $that->value[0] ) {
			return $that;
		}

		$value = Calculator::get()->gcd( $this->value, $that->value );

		return new BigInteger( $value );
	}

	/**
	 * Returns the absolute value of this number.
	 *
	 * @return BigInteger
	 */
	public function abs() {
		return $this->isNegative() ? $this->negated() : $this;
	}

	/**
	 * Returns the inverse of this number.
	 *
	 * @return BigInteger
	 */
	public function negated() {
		return new BigInteger( Calculator::get()->neg( $this->value ) );
	}

	/**
	 * Compares this number to the given one.
	 *
	 * @param BigNumber|number|string $that Number to compare to.
	 *
	 * @return int [-1,0,1] If `$this` is lower than, equal to, or greater than `$that`.
	 *
	 * @throws MathException If the number is not valid.
	 */
	public function compareTo( $that ) {
		$that = BigNumber::of( $that );

		if ( $that instanceof BigInteger ) {
			return Calculator::get()->cmp( $this->value, $that->value );
		}

		return - $that->compareTo( $this );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSign() {
		return ( '0' === $this->value ) ? 0 : ( ( '-' === $this->value[0] ) ? -1 : 1 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigInteger() {
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigDecimal() {
		return BigDecimal::create( $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigRational() {
		return BigRational::create( $this, self::one(), false );
	}

	/**
	 * Converts this number to a BigDecimal with the given scale, using rounding if necessary.
	 *
	 * @param int $scale         The scale of the resulting `BigDecimal`.
	 * @param int $rounding_mode A `RoundingMode` constant.
	 *
	 * @return BigDecimal
	 *
	 * @throws RoundingNecessaryException If this number cannot be converted to the given scale without rounding.
	 *                                    This only applies when RoundingMode::UNNECESSARY is used.
	 */
	public function toScale( $scale, $rounding_mode = RoundingMode::UNNECESSARY ) {
		return $this->toBigDecimal()->toScale( $scale, $rounding_mode );
	}

	/**
	 * Returns the exact value of this number as a native integer.
	 *
	 * If this number cannot be converted to a native integer without losing precision, an exception is thrown.
	 * Note that the acceptable range for an integer depends on the platform and differs for 32-bit and 64-bit.
	 *
	 * @return int The converted value.
	 *
	 * @throws IntegerOverflowException If this number cannot be exactly converted to a native integer.
	 */
	public function toInt() {
		if ( $this->isLessThan( PHP_INT_MIN ) || $this->isGreaterThan( PHP_INT_MAX ) ) {
			throw IntegerOverflowException::toIntOverflow( $this );
		}

		return (int) $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toFloat() {
		return (float) $this->value;
	}

	/**
	 * Returns a string representation of this number in the given base.
	 *
	 * @param int $base Base to use.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException If the base is out of range.
	 */
	public function toBase( $base ) {
		if ( 10 === $base ) {
			return $this->value;
		}

		if ( $base < 2 || $base > 36 ) {
			throw new \InvalidArgumentException( sprintf( 'Base %d is out of range [2, 36]', $base ) );
		}

		$dictionary = '0123456789abcdefghijklmnopqrstuvwxyz';

		$calc = Calculator::get();

		$value    = $this->value;
		$negative = ( '-' === $value[0] );

		if ( $negative ) {
			$value = substr( $value, 1 );
		}

		$base   = (string) $base;
		$result = '';

		while ( '0' !== $value ) {
			list($value, $remainder) = $calc->div_q_r( $value, $base );
			$remainder               = (int) $remainder;

			$result .= $dictionary[ $remainder ];
		}

		if ( $negative ) {
			$result .= '-';
		}

		return strrev( $result );
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return $this->value;
	}

	/**
	 * This method is required by interface Serializable and SHOULD NOT be accessed directly.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function serialize() {
		return $this->value;
	}

	/**
	 * This method is only here to implement interface Serializable and cannot be accessed directly.
	 *
	 * @internal
	 *
	 * @param string $value Value to unserialize.
	 *
	 * @return void
	 *
	 * @throws \LogicException If called directly.
	 */
	public function unserialize( $value ) {
		if ( null !== $this->value ) {
			throw new \LogicException( 'unserialize() is an internal function, it must not be called directly.' );
		}

		$this->value = $value;
	}
}
