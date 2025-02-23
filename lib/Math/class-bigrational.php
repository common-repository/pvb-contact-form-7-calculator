<?php
/**
 * File: class-bignumber.php
 *
 * Defines the BigNumber class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

use PVBCF7Calculator\lib\Math\Exception\DivisionByZeroException;
use PVBCF7Calculator\lib\Math\Exception\MathException;
use PVBCF7Calculator\lib\Math\Exception\NumberFormatException;
use PVBCF7Calculator\lib\Math\Exception\RoundingNecessaryException;

/**
 * An arbitrarily large rational number.
 *
 * This class is immutable.
 */
final class BigRational extends BigNumber {

	/**
	 * The numerator.
	 *
	 * @var BigInteger
	 */
	private $numerator;

	/**
	 * The denominator. Always strictly positive.
	 *
	 * @var BigInteger
	 */
	private $denominator;

	/**
	 * Protected constructor. Use a factory method to obtain an instance.
	 *
	 * @param BigInteger $numerator         The numerator.
	 * @param BigInteger $denominator       The denominator.
	 * @param bool       $check_denominator Whether to check the denominator for negative and zero.
	 *
	 * @throws DivisionByZeroException If the denominator is zero.
	 */
	protected function __construct( $numerator, $denominator, $check_denominator ) {
		if ( $check_denominator ) {
			if ( $denominator->isZero() ) {
				throw DivisionByZeroException::denominatorMustNotBeZero();
			}

			if ( $denominator->isNegative() ) {
				$numerator   = $numerator->negated();
				$denominator = $denominator->negated();
			}
		}

		$this->numerator   = $numerator;
		$this->denominator = $denominator;
	}

	/**
	 * Creates a BigRational of the given value.
	 *
	 * @param BigNumber|number|string $value Value to convert.
	 *
	 * @return BigRational
	 *
	 * @throws MathException If the value cannot be converted to a BigRational.
	 */
	public static function of( $value ) {
		return parent::of( $value )->toBigRational();
	}

	/**
	 * Creates a BigRational out of a numerator and a denominator.
	 *
	 * If the denominator is negative, the signs of both the numerator and the denominator
	 * will be inverted to ensure that the denominator is always positive.
	 *
	 * @param BigNumber|number|string $numerator   The numerator. Must be convertible to a BigInteger.
	 * @param BigNumber|number|string $denominator The denominator. Must be convertible to a BigInteger.
	 *
	 * @return BigRational
	 *
	 * @throws NumberFormatException      If an argument does not represent a valid number.
	 * @throws RoundingNecessaryException If an argument represents a non-integer number.
	 * @throws DivisionByZeroException    If the denominator is zero.
	 */
	public static function nd( $numerator, $denominator ) {
		$numerator   = BigInteger::of( $numerator );
		$denominator = BigInteger::of( $denominator );

		return new BigRational( $numerator, $denominator, true );
	}

	/**
	 * Returns a BigRational representing zero.
	 *
	 * @return BigRational
	 */
	public static function zero() {
		static $zero;

		if ( null === $zero ) {
			$zero = new BigRational( BigInteger::zero(), BigInteger::one(), false );
		}

		return $zero;
	}

	/**
	 * Returns a BigRational representing one.
	 *
	 * @return BigRational
	 */
	public static function one() {
		static $one;

		if ( null === $one ) {
			$one = new BigRational( BigInteger::one(), BigInteger::one(), false );
		}

		return $one;
	}

	/**
	 * Returns a BigRational representing ten.
	 *
	 * @return BigRational
	 */
	public static function ten() {
		static $ten;

		if ( null === $ten ) {
			$ten = new BigRational( BigInteger::ten(), BigInteger::one(), false );
		}

		return $ten;
	}

	/**
	 * Returns the numerator.
	 *
	 * @return BigInteger
	 */
	public function getNumerator() {
		return $this->numerator;
	}

	/**
	 * Returns the denominator.
	 *
	 * @return BigInteger
	 */
	public function getDenominator() {
		return $this->denominator;
	}

	/**
	 * Returns the quotient of the division of the numerator by the denominator.
	 *
	 * @return BigInteger
	 */
	public function quotient() {
		return $this->numerator->quotient( $this->denominator );
	}

	/**
	 * Returns the remainder of the division of the numerator by the denominator.
	 *
	 * @return BigInteger
	 */
	public function remainder() {
		return $this->numerator->remainder( $this->denominator );
	}

	/**
	 * Returns the quotient and remainder of the division of the numerator by the denominator.
	 *
	 * @return BigInteger[]
	 */
	public function quotientAndRemainder() {
		return $this->numerator->quotientAndRemainder( $this->denominator );
	}

	/**
	 * Returns the sum of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The number to add.
	 *
	 * @return BigRational The result.
	 *
	 * @throws MathException If the number is not valid.
	 */
	public function plus( $that ) {
		$that = self::of( $that );

		$numerator   = $this->numerator->multipliedBy( $that->denominator );
		$numerator   = $numerator->plus( $that->numerator->multipliedBy( $this->denominator ) );
		$denominator = $this->denominator->multipliedBy( $that->denominator );

		return new BigRational( $numerator, $denominator, false );
	}

	/**
	 * Returns the difference of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The number to subtract.
	 *
	 * @return BigRational The result.
	 *
	 * @throws MathException If the number is not valid.
	 */
	public function minus( $that ) {
		$that = self::of( $that );

		$numerator   = $this->numerator->multipliedBy( $that->denominator );
		$numerator   = $numerator->minus( $that->numerator->multipliedBy( $this->denominator ) );
		$denominator = $this->denominator->multipliedBy( $that->denominator );

		return new BigRational( $numerator, $denominator, false );
	}

	/**
	 * Returns the product of this number and the given one.
	 *
	 * @param BigNumber|number|string $that The multiplier.
	 *
	 * @return BigRational The result.
	 *
	 * @throws MathException If the multiplier is not a valid number.
	 */
	public function multipliedBy( $that ) {
		$that = self::of( $that );

		$numerator   = $this->numerator->multipliedBy( $that->numerator );
		$denominator = $this->denominator->multipliedBy( $that->denominator );

		return new BigRational( $numerator, $denominator, false );
	}

	/**
	 * Returns the result of the division of this number by the given one.
	 *
	 * @param BigNumber|number|string $that The divisor.
	 *
	 * @return BigRational The result.
	 *
	 * @throws MathException If the divisor is not a valid number, or is zero.
	 */
	public function dividedBy( $that ) {
		$that = self::of( $that );

		$numerator   = $this->numerator->multipliedBy( $that->denominator );
		$denominator = $this->denominator->multipliedBy( $that->numerator );

		return new BigRational( $numerator, $denominator, true );
	}

	/**
	 * Returns this number exponentiated to the given value.
	 *
	 * @param int $exponent The exponent.
	 *
	 * @return BigRational The result.
	 *
	 * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
	 */
	public function power( $exponent ) {
		if ( 0 === $exponent ) {
			$one = BigInteger::one();

			return new BigRational( $one, $one, false );
		}

		if ( 1 === $exponent ) {
			return $this;
		}

		return new BigRational(
			$this->numerator->power( $exponent ),
			$this->denominator->power( $exponent ),
			false
		);
	}

	/**
	 * Returns the reciprocal of this BigRational.
	 *
	 * The reciprocal has the numerator and denominator swapped.
	 *
	 * @return BigRational
	 *
	 * @throws DivisionByZeroException If the numerator is zero.
	 */
	public function reciprocal() {
		return new BigRational( $this->denominator, $this->numerator, true );
	}

	/**
	 * Returns the absolute value of this BigRational.
	 *
	 * @return BigRational
	 */
	public function abs() {
		return new BigRational( $this->numerator->abs(), $this->denominator, false );
	}

	/**
	 * Returns the negated value of this BigRational.
	 *
	 * @return BigRational
	 */
	public function negated() {
		return new BigRational( $this->numerator->negated(), $this->denominator, false );
	}

	/**
	 * Returns the simplified value of this BigRational.
	 *
	 * @return BigRational
	 */
	public function simplified() {
		$gcd = $this->numerator->gcd( $this->denominator );

		$numerator   = $this->numerator->quotient( $gcd );
		$denominator = $this->denominator->quotient( $gcd );

		return new BigRational( $numerator, $denominator, false );
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
		return $this->minus( $that )->getSign();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSign() {
		return $this->numerator->getSign();
	}

	/**
	 * Converts this number to a BigInteger.
	 *
	 * @return BigInteger The converted number.
	 *
	 * @throws RoundingNecessaryException If this number cannot be converted to a BigInteger without rounding.
	 */
	public function toBigInteger() {
		$simplified = $this->simplified();

		if ( ! $simplified->denominator->isEqualTo( 1 ) ) {
			throw new RoundingNecessaryException( 'This rational number cannot be represented as an integer value without rounding.' );
		}

		return $simplified->numerator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigDecimal() {
		return $this->numerator->toBigDecimal()->exactlyDividedBy( $this->denominator );
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBigRational() {
		return $this;
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
		return $this->numerator->toBigDecimal()->dividedBy( $this->denominator, $scale, $rounding_mode );
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
		return $this->numerator->toFloat() / $this->denominator->toFloat();
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		$numerator   = (string) $this->numerator;
		$denominator = (string) $this->denominator;

		if ( '1' === $denominator ) {
			return $numerator;
		}

		return $this->numerator . '/' . $this->denominator;
	}

	/**
	 * This method is required by interface Serializable and SHOULD NOT be accessed directly.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function serialize() {
		return $this->numerator . '/' . $this->denominator;
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
		if ( null !== $this->numerator || null !== $this->denominator ) {
			throw new \LogicException( 'unserialize() is an internal function, it must not be called directly.' );
		}

		list($numerator, $denominator) = explode( '/', $value );

		$this->numerator   = BigInteger::of( $numerator );
		$this->denominator = BigInteger::of( $denominator );
	}
}
