<?php
/**
 * File: class-mathexception.php
 *
 * Defines the MathException class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Exception;

/**
 * Base class for all math exceptions.
 *
 * This class is abstract to ensure that only fine-grained exceptions are thrown throughout the code.
 */
abstract class MathException extends \RuntimeException {

}
