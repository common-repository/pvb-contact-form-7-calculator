<?php
/**
 * File: class-nativecalculator.php
 *
 * Defines the NativeCalculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math\Internal\Calculator;

use PVBCF7Calculator\lib\Math\Internal\Calculator;

/**
 * Calculator implementation using only native PHP code.
 *
 * @internal
 */
class NativeCalculator extends Calculator {

	/**
	 * The max number of digits the platform can natively add, subtract or divide without overflow.
	 *
	 * @var int
	 */
	private $max_digits_add_div = 0;

	/**
	 * The max number of digits the platform can natively multiply without overflow.
	 *
	 * @var int
	 */
	private $max_digits_mul = 0;

	/**
	 * Class constructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		switch ( PHP_INT_SIZE ) {
			case 4:
				$this->max_digits_add_div = 9;
				$this->max_digits_mul     = 4;
				break;

			case 8:
				$this->max_digits_add_div = 18;
				$this->max_digits_mul     = 9;
				break;
		}
	}

	/**
	 * Adds two numbers.
	 *
	 * @param string $a The augend.
	 * @param string $b The addend.
	 *
	 * @return string The sum.
	 */
	public function add( $a, $b ) {
		if ( '0' === $a ) {
			return $b;
		}

		if ( '0' === $b ) {
			return $a;
		}

		$this->init( $a, $b, $a_dig, $b_dig, $a_neg, $b_neg, $a_len, $b_len );

		if ( $a_len <= $this->max_digits_add_div && $b_len <= $this->max_digits_add_div ) {
			return (string) ( (int) $a + (int) $b );
		}

		if ( $a_neg === $b_neg ) {
			$result = $this->doAdd( $a_dig, $b_dig, $a_len, $b_len );
		} else {
			$result = $this->doSub( $a_dig, $b_dig, $a_len, $b_len );
		}

		if ( $a_neg ) {
			$result = $this->neg( $result );
		}

		return $result;
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
		return $this->add( $a, $this->neg( $b ) );
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
		if ( '0' === $a || '0' === $b ) {
			return '0';
		}

		if ( '1' === $a ) {
			return $b;
		}

		if ( '1' === $b ) {
			return $a;
		}

		if ( '-1' === $a ) {
			return $this->neg( $b );
		}

		if ( '-1' === $b ) {
			return $this->neg( $a );
		}

		$this->init( $a, $b, $a_dig, $b_dig, $a_neg, $b_neg, $a_len, $b_len );

		if ( $a_len <= $this->max_digits_mul && $b_len <= $this->max_digits_mul ) {
			return (string) ( (int) $a * (int) $b );
		}

		$result = $this->doMul( $a_dig, $b_dig, $a_len, $b_len );

		if ( $a_neg !== $b_neg ) {
			$result = $this->neg( $result );
		}

		return $result;
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
		return $this->div_q_r( $a, $b )[0];
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
		return $this->div_q_r( $a, $b )[1];
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
		if ( '0' === $a ) {
			return array( '0', '0' );
		}

		if ( $a === $b ) {
			return array( '1', '0' );
		}

		if ( '1' === $b ) {
			return array( $a, '0' );
		}

		if ( '-1' === $b ) {
			return array( $this->neg( $a ), '0' );
		}

		$this->init( $a, $b, $a_dig, $b_dig, $a_neg, $b_neg, $a_len, $b_len );

		if ( $a_len <= $this->max_digits_add_div && $b_len <= $this->max_digits_add_div ) {
			$a = (int) $a;
			$b = (int) $b;

			$r = $a % $b;
			$q = ( $a - $r ) / $b;

			$q = (string) $q;
			$r = (string) $r;

			return array( $q, $r );
		}

		list($q, $r) = $this->doDiv( $a_dig, $b_dig, $a_len, $b_len );

		if ( $a_neg !== $b_neg ) {
			$q = $this->neg( $q );
		}

		if ( $a_neg ) {
			$r = $this->neg( $r );
		}

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
		if ( 0 === $e ) {
			return '1';
		}

		if ( 1 === $e ) {
			return $a;
		}

		$odd = $e % 2;
		$e  -= $odd;

		$aa     = $this->mul( $a, $a );
		$result = $this->pow( $aa, $e / 2 );

		if ( 1 === $odd ) {
			$result = $this->mul( $result, $a );
		}

		return $result;
	}

	/**
	 * Performs the addition of two non-signed large integers.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return string
	 */
	private function doAdd( $a, $b, $x, $y ) {
		$length = $this->pad( $a, $b, $x, $y );

		$carry  = 0;
		$result = '';

		for ( $i = $length - 1; $i >= 0; $i-- ) {
			$sum = (int) $a[ $i ] + (int) $b[ $i ] + $carry;

			if ( $sum >= 10 ) {
				$carry = 1;
				$sum  -= 10;
			} else {
				$carry = 0;
			}

			$result .= $sum;
		}

		if ( 0 !== $carry ) {
			$result .= $carry;
		}

		return strrev( $result );
	}

	/**
	 * Performs the subtraction of two non-signed large integers.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return string
	 */
	private function doSub( $a, $b, $x, $y ) {
		if ( $a === $b ) {
			return '0';
		}

		$cmp = $this->doCmp( $a, $b, $x, $y );

		$invert = ( -1 === $cmp );

		if ( $invert ) {
			$c = $a;
			$a = $b;
			$b = $c;

			$z = $x;
			$x = $y;
			$y = $z;
		}

		$length = $this->pad( $a, $b, $x, $y );

		$carry  = 0;
		$result = '';

		for ( $i = $length - 1; $i >= 0; $i-- ) {
			$sum = (int) $a[ $i ] - (int) $b[ $i ] - $carry;

			if ( $sum < 0 ) {
				$carry = 1;
				$sum  += 10;
			} else {
				$carry = 0;
			}

			$result .= $sum;
		}

		$result = strrev( $result );
		$result = ltrim( $result, '0' );

		if ( $invert ) {
			$result = $this->neg( $result );
		}

		return $result;
	}

	/**
	 * Performs the multiplication of two non-signed large integers.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return string
	 */
	private function doMul( $a, $b, $x, $y ) {
		$result = '0';

		for ( $i = $x - 1; $i >= 0; $i-- ) {
			$line  = str_repeat( '0', $x - 1 - $i );
			$carry = 0;
			for ( $j = $y - 1; $j >= 0; $j-- ) {
				$mul   = (int) $a[ $i ] * (int) $b[ $j ] + $carry;
				$digit = $mul % 10;
				$carry = ( $mul - $digit ) / 10;
				$line .= $digit;
			}

			if ( 0 !== $carry ) {
				$line .= $carry;
			}

			$line = rtrim( $line, '0' );

			if ( '' !== $line ) {
				$result = $this->add( $result, strrev( $line ) );
			}
		}

		return $result;
	}

	/**
	 * Performs the division of two non-signed large integers.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return string[] The quotient and remainder.
	 */
	private function doDiv( $a, $b, $x, $y ) {
		$cmp = $this->doCmp( $a, $b, $x, $y );

		if ( -1 === $cmp ) {
			return array( '0', $a );
		}

		// We now know that (a > b && x >= y).

		$q = '0'; // Quotient.
		$r = $a; // Remainder.
		$z = $y; // Focus length, always $y or ($y + 1).

		for ( ;; ) {
			$focus = substr( $a, 0, $z );

			$cmp = $this->doCmp( $focus, $b, $z, $y );

			if ( -1 === $cmp ) {
				if ( $z === $x ) { // Remainder < dividend.
					break;
				}

				$z++;
			}

			$zeros = str_repeat( '0', $x - $z );

			$q = $this->add( $q, '1' . $zeros );
			$a = $this->sub( $a, $b . $zeros );

			$r = $a;

			if ( '0' === $r ) { // Remainder == 0.
				break;
			}

			$x = strlen( $a );

			if ( $x < $y ) { // Remainder < dividend.
				break;
			}

			$z = $y;
		}

		return array( $q, $r );
	}

	/**
	 * Compares two non-signed large numbers.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return int [-1, 0, 1]
	 */
	private function doCmp( $a, $b, $x, $y ) {
		if ( $x > $y ) {
			return 1;
		}
		if ( $x < $y ) {
			return -1;
		}

		for ( $i = 0; $i < $x; $i++ ) {
			$ai = (int) $a[ $i ];
			$bi = (int) $b[ $i ];

			if ( $ai > $bi ) {
				return 1;
			}
			if ( $ai < $bi ) {
				return -1;
			}
		}

		return 0;
	}

	/**
	 * Pads the left of one of the given numbers with zeros if necessary to make both numbers the same length.
	 *
	 * The numbers must only consist of digits, without leading minus sign.
	 *
	 * @param string $a The first operand.
	 * @param string $b The second operand.
	 * @param int    $x The length of the first operand.
	 * @param int    $y The length of the second operand.
	 *
	 * @return int The length of both strings.
	 */
	private function pad( &$a, &$b, $x, $y ) {
		if ( $x === $y ) {
			return $x;
		}

		if ( $x < $y ) {
			$a = str_repeat( '0', $y - $x ) . $a;

			return $y;
		}

		$b = str_repeat( '0', $x - $y ) . $b;

		return $x;
	}
}
