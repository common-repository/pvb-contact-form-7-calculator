<?php
/**
 * File: class-roundingnecessaryexception.php
 *
 * Defines the RoundingNecessaryException class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Exception;

/**
 * Exception thrown when a number cannot be represented at the requested scale without rounding.
 */
class RoundingNecessaryException extends MathException {

	/**
	 * Rounding necessary (scale mismatch exception).
	 *
	 * @return RoundingNecessaryException
	 */
	public static function roundingNecessary() {
		return new self( 'Rounding is necessary to represent the result of the operation at this scale.' );
	}
}
