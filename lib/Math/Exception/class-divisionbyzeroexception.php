<?php
/**
 * File: class-divisionbyzeroexception.php
 *
 * Defines the DivisionByZeroException class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 */
class DivisionByZeroException extends MathException {

	/**
	 * Division by zero.
	 *
	 * @return DivisionByZeroException
	 */
	public static function divisionByZero() {
		return new self( 'Division by zero.' );
	}

	/**
	 * Division by zero when defining a rational number.
	 *
	 * @return DivisionByZeroException
	 */
	public static function denominatorMustNotBeZero() {
		return new self( 'The denominator of a rational number cannot be zero.' );
	}
}
