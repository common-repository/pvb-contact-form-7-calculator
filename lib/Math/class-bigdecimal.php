<?php
/**
 * File: class-bigdecimal.php
 *
 * Defines the BigDecimal class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

use PVBCF7Calculator\lib\Math\Exception\DivisionByZeroException;
use PVBCF7Calculator\lib\Math\Exception\MathException;
use PVBCF7Calculator\lib\Math\Internal\Calculator;

/**
 * Immutable, arbitrary-precision signed decimal numbers.
 */
final class BigDecimal extends BigNumber {

	/**
	 * The unscaled value of this decimal number.
	 *
	 * This is a string of digits with an optional leading minus sign.
	 * No leading zero must be present.
	 * No leading minus sign must be present if the value is 0.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * The scale (number of digits after the decimal point) of this decimal number.
	 *
	 * This must be zero or more.
	 *
	 * @var int
	 */
	private $scale;

	/**
	 * Protected constructor. Use a factory method to obtain an instance.
	 *
	 * @param string $value The unscaled value, validated.
	 * @param int    $scale The scale, validated.
	 */
	protected function __construct( $value, $scale = 0 ) {
		$this->value = $value;
		$this->scale = $scale;
	}

	/**
	 * Creates a BigDecimal of the given value.
	 *
	 * @param BigNumber|number|string $value Value to convert.
	 *
	 * @return BigDecimal
	 *
	 * @throws MathException If the value cannot be converted to a BigDecimal.
	 */
	public static function of( $value ) {
		return parent::of( $value )->toBigDecimal();
	}

	/**
	 * Creates a BigDecimal from an unscaled value and a scale.
	 *
	 * Example: `(12345, 3)` will result in the BigDecimal `12.345`.
	 *
	 * @param BigNumber|number|string $value The unscaled value. Must be convertible to a BigInteger.
	 * @param int                     $scale The scale of the number, positive or zero.
	 *
	 * @return BigDecimal
	 *
	 * @throws \InvalidArgumentException If the scale is negative.
	 */
	public static function ofUnscaledValue( $value, $scale = 0 ) {
		if ( $scale < 0 ) {
			throw new \InvalidArgumentException( 'The scale cannot be negative.' );
		}

		return new BigDecimal( (string) BigInteger::of( $value ), $scale );
	}

	/**
	 * Returns a BigDecimal representing zero, with a scale of zero.
	 *
	 * @return BigDecimal
	 */
	public static function zero() {
		static $zero = null;

		if ( null === $zero ) {
			$zero = new BigDecimal( '0' );
		}

		return $zero;
	}

	/**
	 * Returns a BigDecimal representing one, with a scale of zero.
	 *
	 * @return BigDecimal
	 */
	public static function one() {
		static $one = null;

		if ( null === $one ) {
			$one = new BigDecimal( '1' );
		}

		return $one;
	}

	/**
	 * Returns a BigDecimal representing ten, with a scale of zero.
	 *
	 * @return BigDecimal
	 */
	public static function ten() {
		static $ten = null;

		if ( null === $ten ) {
			$ten = new BigDecimal( '10' );
		}

		return $ten;
	}

	/**
	 * Returns the sum of this number and the given one.
	 *
	 * The result has a scale of `max($this->scale, $that->scale)`.
	 *
	 * @param BigNumber|number|string $that The number to add. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The result.
	 *
	 * @throws MathException If the number is not valid, or is not convertible to a BigDecimal.
	 */
	public function plus( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value && $that->scale <= $this->scale ) {
			return $this;
		}

		$this->scaleValues( $this, $that, $a, $b );

		$value = Calculator::get()->add( $a, $b );
		$scale = $this->scale > $that->scale ? $this->scale : $that->scale;

		return new BigDecimal( $value, $scale );
	}

	/**
	 * Returns the difference of this number and the given one.
	 *
	 * The result has a scale of `max($this->scale, $that->scale)`.
	 *
	 * @param BigNumber|number|string $that The number to subtract. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The result.
	 *
	 * @throws MathException If the number is not valid, or is not convertible to a BigDecimal.
	 */
	public function minus( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value && $that->scale <= $this->scale ) {
			return $this;
		}

		$this->scaleValues( $this, $that, $a, $b );

		$value = Calculator::get()->sub( $a, $b );
		$scale = $this->scale > $that->scale ? $this->scale : $that->scale;

		return new BigDecimal( $value, $scale );
	}

	/**
	 * Returns the product of this number and the given one.
	 *
	 * The result has a scale of `$this->scale + $that->scale`.
	 *
	 * @param BigNumber|number|string $that The multiplier. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The result.
	 *
	 * @throws MathException If the multiplier is not a valid number, or is not convertible to a BigDecimal.
	 */
	public function multipliedBy( $that ) {
		$that = self::of( $that );

		if ( '1' === $that->value && 0 === $that->scale ) {
			return $this;
		}

		$value = Calculator::get()->mul( $this->value, $that->value );
		$scale = $this->scale + $that->scale;

		return new BigDecimal( $value, $scale );
	}

	/**
	 * Returns the result of the division of this number by the given one, at the given scale.
	 *
	 * @param BigNumber|number|string $that          The divisor.
	 * @param int|null                $scale         The desired scale, or null to use the scale of this number.
	 * @param int                     $rounding_mode An optional rounding mode.
	 *
	 * @return BigDecimal
	 *
	 * @throws \InvalidArgumentException If the scale or rounding mode is invalid.
	 * @throws DivisionByZeroException   If the divisor is not a valid decimal number, or is zero.
	 */
	public function dividedBy( $that, $scale = null, $rounding_mode = RoundingMode::UNNECESSARY ) {
		$that = self::of( $that );

		if ( $that->isZero() ) {
			throw DivisionByZeroException::divisionByZero();
		}

		if ( null === $scale ) {
			$scale = $this->scale;
		} elseif ( $scale < 0 ) {
			throw new \InvalidArgumentException( 'Scale cannot be negative.' );
		}

		if ( '1' === $that->value && 0 === $that->scale && $scale === $this->scale ) {
			return $this;
		}

		$p = $this->valueWithMinScale( $that->scale + $scale );
		$q = $that->valueWithMinScale( $this->scale - $scale );

		$result = Calculator::get()->div_round( $p, $q, $rounding_mode );

		return new BigDecimal( $result, $scale );
	}

	/**
	 * Returns the exact result of the division of this number by the given one.
	 *
	 * The scale of the result is automatically calculated to fit all the fraction digits.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The result.
	 *
	 * @throws DivisionByZeroException If the divisor is not a valid decimal number, or is zero.
	 */
	public function exactlyDividedBy( $that ) {
		$that = self::of( $that );

		if ( '0' === $that->value ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$this->scaleValues( $this, $that, $a, $b );

		$d     = rtrim( $b, '0' );
		$scale = strlen( $b ) - strlen( $d );

		$calculator = Calculator::get();

		foreach ( array( 5, 2 ) as $prime ) {
			for ( ;; ) {
				$last_digit = (int) substr( $d, -1 );

				if ( 0 !== $last_digit % $prime ) {
					break;
				}

				$d = $calculator->div_q( $d, (string) $prime );
				$scale++;
			}
		}

		return $this->dividedBy( $that, $scale )->stripTrailingZeros();
	}

	/**
	 * Returns this number exponentiated to the given value.
	 *
	 * The result has a scale of `$this->scale * $exponent`.
	 *
	 * @param int $exponent The exponent.
	 *
	 * @return BigDecimal The result.
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

		return new BigDecimal( Calculator::get()->pow( $this->value, $exponent ), $this->scale * $exponent );
	}

	/**
	 * Returns the quotient of the division of this number by this given one.
	 *
	 * The quotient has a scale of `0`.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The quotient.
	 *
	 * @throws DivisionByZeroException If the divisor is not a valid decimal number, or is zero.
	 */
	public function quotient( $that ) {
		$that = self::of( $that );

		if ( $that->isZero() ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$p = $this->valueWithMinScale( $that->scale );
		$q = $that->valueWithMinScale( $this->scale );

		$quotient = Calculator::get()->div_q( $p, $q );

		return new BigDecimal( $quotient, 0 );
	}

	/**
	 * Returns the remainder of the division of this number by this given one.
	 *
	 * The remainder has a scale of `max($this->scale, $that->scale)`.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal The remainder.
	 *
	 * @throws DivisionByZeroException If the divisor is not a valid decimal number, or is zero.
	 */
	public function remainder( $that ) {
		$that = self::of( $that );

		if ( $that->isZero() ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$p = $this->valueWithMinScale( $that->scale );
		$q = $that->valueWithMinScale( $this->scale );

		$remainder = Calculator::get()->div_r( $p, $q );

		$scale = $this->scale > $that->scale ? $this->scale : $that->scale;

		return new BigDecimal( $remainder, $scale );
	}

	/**
	 * Returns the quotient and remainder of the division of this number by the given one.
	 *
	 * The quotient has a scale of `0`, and the remainder has a scale of `max($this->scale, $that->scale)`.
	 *
	 * @param BigNumber|number|string $that The divisor. Must be convertible to a BigDecimal.
	 *
	 * @return BigDecimal[] An array containing the quotient and the remainder.
	 *
	 * @throws DivisionByZeroException If the divisor is not a valid decimal number, or is zero.
	 */
	public function quotientAndRemainder( $that ) {
		$that = self::of( $that );

		if ( $that->isZero() ) {
			throw DivisionByZeroException::divisionByZero();
		}

		$p = $this->valueWithMinScale( $that->scale );
		$q = $that->valueWithMinScale( $this->scale );

		$r         = Calculator::get()->div_q_r( $p, $q );
		$quotient  = $r[0];
		$remainder = $r[1];

		$scale = $this->scale > $that->scale ? $this->scale : $that->scale;

		$quotient  = new BigDecimal( $quotient, 0 );
		$remainder = new BigDecimal( $remainder, $scale );

		return array( $quotient, $remainder );
	}

	/**
	 * Returns a copy of this BigDecimal with the decimal point moved $n places to the left.
	 *
	 * @param int $n Number of places to move the decimal point.
	 *
	 * @return BigDecimal
	 */
	public function withPointMovedLeft( $n ) {
		if ( 0 === $n ) {
			return $this;
		}

		if ( $n < 0 ) {
			return $this->withPointMovedRight( -$n );
		}

		return new BigDecimal( $this->value, $this->scale + $n );
	}

	/**
	 * Returns a copy of this BigDecimal with the decimal point moved $n places to the right.
	 *
	 * @param int $n Number of places to move the decimal point.
	 *
	 * @return BigDecimal
	 */
	public function withPointMovedRight( $n ) {
		if ( 0 === $n ) {
			return $this;
		}

		if ( $n < 0 ) {
			return $this->withPointMovedLeft( -$n );
		}

		$value = $this->value;
		$scale = $this->scale - $n;

		if ( $scale < 0 ) {
			if ( '0' !== $value ) {
				$value .= str_repeat( '0', -$scale );
			}
			$scale = 0;
		}

		return new BigDecimal( $value, $scale );
	}

	/**
	 * Returns a copy of this BigDecimal with any trailing zeros removed from the fractional part.
	 *
	 * @return BigDecimal
	 */
	public function stripTrailingZeros() {
		if ( 0 === $this->scale ) {
			return $this;
		}

		$trimmed_value = rtrim( $this->value, '0' );

		if ( '' === $trimmed_value ) {
			return self::zero();
		}

		$trimmable_zeros = strlen( $this->value ) - strlen( $trimmed_value );

		if ( 0 === $trimmable_zeros ) {
			return $this;
		}

		if ( $trimmable_zeros > $this->scale ) {
			$trimmable_zeros = $this->scale;
		}

		$value = substr( $this->value, 0, -$trimmable_zeros );
		$scale = $this->scale - $trimmable_zeros;

		return new BigDecimal( $value, $scale );
	}

	/**
	 * Returns the absolute value of this number.
	 *
	 * @return BigDecimal
	 */
	public function abs() {
		return $this->isNegative() ? $this->negated() : $this;
	}

	/**
	 * Returns the negated value of this number.
	 *
	 * @return BigDecimal
	 */
	public function negated() {
		return new BigDecimal( Calculator::get()->neg( $this->value ), $this->scale );
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
			$that = $that->toBigDecimal();
		}

		if ( $that instanceof BigDecimal ) {
			$this->scaleValues( $this, $that, $a, $b );

			return Calculator::get()->cmp( $a, $b );
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
	 * Returns the unscaled value.
	 *
	 * @return BigInteger
	 */
	public function getUnscaledValue() {
		return BigInteger::create( $this->value );
	}

	/**
	 * Returns the scale.
	 *
	 * @return int
	 */
	public function getScale() {
		return $this->scale;
	}

	/**
	 * Returns a string representing the integral part of this decimal number.
	 *
	 * Example: `-123.456` => `-123`.
	 *
	 * @return string
	 */
	public function getIntegral() {
		if ( 0 === $this->scale ) {
			return $this->value;
		}

		$value = $this->getUnscaledValueWithLeadingZeros();

		return substr( $value, 0, -$this->scale );
	}

	/**
	 * Returns a string representing the fractional part of this decimal number.
	 *
	 * If the scale is zero, an empty string is returned.
	 *
	 * Examples: `-123.456` => '456', `123` => ''.
	 *
	 * @return string
	 */
	public function getFraction() {
		if ( 0 === $this->scale ) {
			return '';
		}

		$value = $this->getUnscaledValueWithLeadingZeros();

		return substr( $value, -$this->scale );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigInteger() {
		if ( 0 === $this->scale ) {
			$zero_scale_decimal = $this;
		} else {
			$zero_scale_decimal = $this->dividedBy( 1, 0 );
		}

		return BigInteger::create( $zero_scale_decimal->value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigDecimal() {
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigRational() {
		$numerator   = BigInteger::create( $this->value );
		$denominator = BigInteger::create( '1' . str_repeat( '0', $this->scale ) );

		return BigRational::create( $numerator, $denominator, false );
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
		if ( $scale === $this->scale ) {
			return $this;
		}

		return $this->dividedBy( self::one(), $scale, $rounding_mode );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toInt() {
		return $this->toBigInteger()->toInt();
	}

	/**
	 * {@inheritdoc}
	 */
	public function toFloat() {
		return (float) (string) $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		if ( 0 === $this->scale ) {
			return $this->value;
		}

		$value = $this->getUnscaledValueWithLeadingZeros();

		return substr( $value, 0, -$this->scale ) . '.' . substr( $value, -$this->scale );
	}

	/**
	 * This method is required by interface Serializable and SHOULD NOT be accessed directly.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function serialize() {
		return $this->value . ':' . $this->scale;
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
		if ( null !== $this->value || null !== $this->scale ) {
			throw new \LogicException( 'unserialize() is an internal function, it must not be called directly.' );
		}

		list($value, $scale) = explode( ':', $value );

		$this->value = $value;
		$this->scale = (int) $scale;
	}

	/**
	 * Puts the internal values of the given decimal numbers on the same scale.
	 *
	 * @param BigDecimal $x The first decimal number.
	 * @param BigDecimal $y The second decimal number.
	 * @param string     $a A variable to store the scaled integer value of $x.
	 * @param string     $b A variable to store the scaled integer value of $y.
	 *
	 * @return void
	 */
	private function scaleValues( $x, $y, &$a, &$b ) {
		$a = $x->value;
		$b = $y->value;

		if ( '0' !== $b && $x->scale > $y->scale ) {
			$b .= str_repeat( '0', $x->scale - $y->scale );
		} elseif ( '0' !== $a && $x->scale < $y->scale ) {
			$a .= str_repeat( '0', $y->scale - $x->scale );
		}
	}

	/**
	 * Zero-pads the value up to $scale
	 *
	 * @param int $scale Minimum scale.
	 *
	 * @return string
	 */
	private function valueWithMinScale( $scale ) {
		$value = $this->value;

		if ( '0' !== $this->value && $scale > $this->scale ) {
			$value .= str_repeat( '0', $scale - $this->scale );
		}

		return $value;
	}

	/**
	 * Adds leading zeros if necessary to the unscaled value to represent the full decimal number.
	 *
	 * @return string
	 */
	private function getUnscaledValueWithLeadingZeros() {
		$value         = $this->value;
		$target_length = $this->scale + 1;
		$negative      = ( '-' === $value[0] );
		$length        = strlen( $value );

		if ( $negative ) {
			$length--;
		}

		if ( $length >= $target_length ) {
			return $this->value;
		}

		if ( $negative ) {
			$value = substr( $value, 1 );
		}

		$value = str_pad( $value, $target_length, '0', STR_PAD_LEFT );

		if ( $negative ) {
			$value = '-' . $value;
		}

		return $value;
	}
}
