<?php
/**
 * File: class-pvbcalculator.php
 *
 * Defines the PVBCalculator class.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

use \DateTime;
use \DateInterval;
use \DatePeriod;

/**
 * Provides support for custom functions when calculating expressions
 */
class PVBCalculator {

	/**
	 * Defined functions.
	 *
	 * @var array
	 */
	private $functions = array();

	/**
	 * Tokenizer.
	 *
	 * @var TokenizerInterface
	 */
	private $tokenizer;

	/**
	 * Proxy method to access protected constructors from sibling classes.
	 *
	 * @internal
	 *
	 * @return static
	 */
	public static function create() {
		return new self( new Tokenizer() );
	}

	/**
	 * Constructor.
	 * Sets expression if provided.
	 * Sets default functions: sqrt(n), ln(n), log(a,b).
	 *
	 * @param TokenizerInterface $tokenizer Tokenizer.
	 */
	public function __construct( $tokenizer ) {
		$this->tokenizer = $tokenizer;

		$this->add_function(
			'sqrt',
			function ( $x ) {
				return sqrt( $x );
			}
		);
		$this->add_function(
			'log',
			function ( $base, $arg ) {
				return log( $arg, $base );
			}
		);
		$this->add_function(
			'fn_day',
			function ( $timestamp ) {
				return gmdate( 'j', $timestamp * 86400 );
			}
		);
		$this->add_function(
			'fn_month',
			function ( $timestamp ) {
				return gmdate( 'n', $timestamp * 86400 );
			}
		);
		$this->add_function(
			'fn_year',
			function ( $timestamp ) {
				return gmdate( 'Y', $timestamp * 86400 );
			}
		);
		$this->add_function(
			'fn_day_of_year',
			function ( $timestamp ) {
				return gmdate( 'z', $timestamp * 86400 );
			}
		);
		$this->add_function(
			'fn_weekday',
			function ( $timestamp ) {
				return gmdate( 'N', $timestamp * 86400 );
			}
		);
		$this->add_function(
			'fn_business_days',
			function ( $timestamp1, $timestamp2 ) {
				$utc          = new DateTimeZone( 'UTC' );
				$working_days = array( 1, 2, 3, 4, 5 ); // Date format = N (1 = Monday, etc).
				$holiday_days = array( '*-12-25', '*-01-01', '2013-12-23' ); // Variable and fixed holidays.

				if ( $timestamp1 < $timestamp2 ) {
					$from = new DateTime( gmdate( 'Y-m-d', $timestamp1 * 86400 ), $utc );
					$to   = new DateTime( gmdate( 'Y-m-d', $timestamp2 * 86400 ), $utc );
				} else {
					$from = new DateTime( gmdate( 'Y-m-d', $timestamp2 * 86400 ), $utc );
					$to   = new DateTime( gmdate( 'Y-m-d', $timestamp1 * 86400 ), $utc );
				}
				$to->modify( '+1 day' );
				$interval = new DateInterval( 'P1D' );
				$periods  = new DatePeriod( $from, $interval, $to );
				$days     = 0;
				foreach ( $periods as $period ) {
					$count++;
					if ( ! in_array( (int) $period->format( 'N' ), $working_days, true ) ) {
						continue;
					}
					if ( in_array( (string) $period->format( 'Y-m-d' ), $holiday_days, true ) ) {
						continue;
					}
					if ( in_array( (string) $period->format( '*-m-d' ), $holiday_days, true ) ) {
						continue;
					}
					$days++;
				}
				return $days;
			}
		);
	}

	/**
	 * Returns the functions.
	 *
	 * @return array
	 */
	public function get_functions() {
		return $this->functions;
	}

	/**
	 * Adds a function.
	 *
	 * @param  string   $name Name of the function (as in arithmetic expressions).
	 * @param  callable $function Interpretation of this function.
	 *
	 * @throws \InvalidArgumentException If function name contains invalid characters.
	 * @throws \Exception                If function already exists.
	 */
	public function add_function( $name, $function ) {
		$name = strtolower( trim( $name ) );

		if ( ! ctype_alpha( str_replace( '_', '', $name ) ) ) {
			throw new \InvalidArgumentException( 'Only letters and underscore are allowed for a name of a function' );
		}

		if ( array_key_exists( $name, $this->functions ) ) {
			throw new \Exception( sprintf( 'Function %s exists', $name ) );
		}

		$reflection   = new \ReflectionFunction( $function );
		$params_count = $reflection->getNumberOfRequiredParameters();

		$this->functions[ $name ] = array(
			'func'        => $function,
			'paramsCount' => $params_count,
		);
	}

	/**
	 * Replaces a function.
	 *
	 * @param string   $name Name of the function.
	 * @param callable $function Interpretation.
	 */
	public function replace_function( $name, $function ) {
		$this->remove_function( $name );
		$this->add_function( $name, $function );
	}

	/**
	 * Removes a function.
	 *
	 * @param string $name Name of function.
	 */
	public function remove_function( $name ) {
		if ( ! array_key_exists( $name, $this->functions ) ) {
			return;
		}

		unset( $this->functions[ $name ] );
	}

	/**
	 * Rearranges tokens according to RPN (Reverse Polish Notation) or
	 * also known as Postfix Notation.
	 *
	 * @param  array $tokens Tokens to rearrange.
	 * @return \SplQueue
	 * @throws \InvalidArgumentException If parentheses are misplaced.
	 */
	private function get_reverse_polish_notation( $tokens ) {
		$queue = new \SplQueue();
		$stack = new \SplStack();

		$tokens_count = count( $tokens );
		for ( $i = 0; $i < $tokens_count; $i++ ) {
			if ( is_numeric( $tokens[ $i ] ) ) {
				// (string + 0) converts to int or float
				$queue->enqueue( $tokens[ $i ] + 0 );
			} elseif ( array_key_exists( $tokens[ $i ], $this->functions ) ) {
				$stack->push( $tokens[ $i ] );
			} elseif ( Tokens::ARG_SEPARATOR === $tokens[ $i ] ) {
				// Checks whether stack contains left parenthesis.
				if ( substr_count( $stack->serialize(), Tokens::PAREN_LEFT ) === 0 ) {
					throw new \InvalidArgumentException( 'Parentheses are misplaced' );
				}

				while ( Tokens::PAREN_LEFT !== $stack->top() ) {
					$queue->enqueue( $stack->pop() );
				}
			} elseif ( in_array( $tokens[ $i ], Tokens::OPERATORS, true ) ) {
				while ( $stack->count() > 0 && in_array( $stack->top(), Tokens::OPERATORS, true )
					&& ( ( $this->is_operator_left_associative( $tokens[ $i ] )
						&& $this->get_operator_precedence( $tokens[ $i ] ) === $this->get_operator_precedence( $stack->top() ) )
					|| ( $this->get_operator_precedence( $tokens[ $i ] ) < $this->get_operator_precedence( $stack->top() ) ) ) ) {
					$queue->enqueue( $stack->pop() );
				}

				$stack->push( $tokens[ $i ] );
			} elseif ( Tokens::PAREN_LEFT === $tokens[ $i ] ) {
				$stack->push( Tokens::PAREN_LEFT );
			} elseif ( Tokens::PAREN_RIGHT === $tokens[ $i ] ) {
				// Checks whether stack contains left parenthesis.
				if ( substr_count( $stack->serialize(), Tokens::PAREN_LEFT ) === 0 ) {
					throw new \InvalidArgumentException( 'Parentheses are misplaced' );
				}

				while ( Tokens::PAREN_LEFT !== $stack->top() ) {
					$queue->enqueue( $stack->pop() );
				}

				$stack->pop();

				if ( $stack->count() > 0 && array_key_exists( $stack->top(), $this->functions ) ) {
					$queue->enqueue( $stack->pop() );
				}
			}
		}

		while ( $stack->count() > 0 ) {
			$queue->enqueue( $stack->pop() );
		}

		return $queue;
	}

	/**
	 * Calculates tokens ordered in RPN.
	 *
	 * @param  \SplQueue $queue Queue to process.
	 * @return int|float Result of the calculation.
	 * @throws \InvalidArgumentException If expression is invalid.
	 */
	private function calculate_from_rpn( $queue ) {
		$stack = new \SplStack();

		while ( $queue->count() > 0 ) {
			$current_token = $queue->dequeue();
			if ( is_numeric( $current_token ) ) {
				$stack->push( $current_token );
			} else {
				if ( in_array( $current_token, Tokens::OPERATORS, true ) ) {
					if ( $stack->count() < 2 ) {
						throw new \InvalidArgumentException( 'Invalid expression' );
					}
					$stack->push( $this->execute_operator( $current_token, $stack->pop(), $stack->pop() ) );
				} elseif ( array_key_exists( $current_token, $this->functions ) ) {
					if ( $stack->count() < $this->functions[ $current_token ]['paramsCount'] ) {
						throw new \InvalidArgumentException( 'Invalid expression' );
					}

					$params = array();
					for ( $i = 0; $i < $this->functions[ $current_token ]['paramsCount']; $i++ ) {
						$params[] = $stack->pop();
					}

					$stack->push( $this->execute_function( $current_token, $params ) );
				}
			}
		}

		if ( $stack->count() === 1 ) {
			return $stack->pop();
		}
		throw new \InvalidArgumentException( 'Invalid expression' );
	}

	/**
	 * Calculates the current arithmetic expression.
	 *
	 * @param string $expression Expression to calculate.
	 * @return float|int Result of the calculation.
	 */
	public function calculate( $expression ) {
		$tokens = $this->tokenizer->tokenize( $expression, array_keys( $this->functions ) );
		$rpn    = $this->get_reverse_polish_notation( $tokens );
		$result = $this->calculate_from_rpn( $rpn );

		return $result;
	}

	/**
	 * Checks association of an operator.
	 *
	 * @param  string $operator A valid operator.
	 * @return bool
	 * @throws \InvalidArgumentException If operator is not valid.
	 */
	private function is_operator_left_associative( $operator ) {
		if ( ! in_array( $operator, Tokens::OPERATORS, true ) ) {
			throw new \InvalidArgumentException( "Cannot check association of $operator operator" );
		}

		if ( Tokens::POW === $operator ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks precedence of an operator.
	 *
	 * @param  string $operator A valid operator.
	 * @return int
	 * @throws \InvalidArgumentException If operator is not valid.
	 */
	private function get_operator_precedence( $operator ) {
		if ( ! in_array( $operator, Tokens::OPERATORS, true ) ) {
			throw new \InvalidArgumentException( "Cannot check precedence of $operator operator" );
		}

		if ( Tokens::POW === $operator ) {
			return 6;
		} elseif ( Tokens::MULT === $operator || Tokens::DIV === $operator ) {
			return 4;
		} elseif ( Tokens::MOD === $operator ) {
			return 2;
		}
		return 1;
	}

	/**
	 * Executes an operator.
	 *
	 * @param  string    $operator A valid operator.
	 * @param  int|float $a First value.
	 * @param  int|float $b Second value.
	 * @return int|float Result.
	 * @throws \InvalidArgumentException If operator is not valid.
	 */
	private function execute_operator( $operator, $a, $b ) {
		if ( Tokens::PLUS === $operator ) {
			$r = BigDecimal::of( $a );
			return $r->plus( $b )->toFloat();
		} elseif ( Tokens::MINUS === $operator ) {
			$r = BigDecimal::of( $b );
			return $r->minus( $a )->toFloat();
		} elseif ( Tokens::MOD === $operator ) {
			return $b % $a;
		} elseif ( Tokens::MULT === $operator ) {
			$r = BigDecimal::of( $a );
			return $r->multipliedBy( $b )->toFloat();
		} elseif ( Tokens::DIV === $operator ) {
			if ( 0 === $a ) {
				throw new \InvalidArgumentException( 'Division by zero occurred' );
			}
			$r = BigDecimal::of( $b );
			return $r->dividedBy( $a, 16, RoundingMode::HALF_UP )->toFloat();
		} elseif ( Tokens::POW === $operator ) {
			return pow( $b, $a );
		}

		throw new \InvalidArgumentException( 'Unknown operator provided' );
	}

	/**
	 * Executes a function.
	 *
	 * @param  string $function_name Name of the function to execute.
	 * @param  array  $params        Parameters to pass.
	 *
	 * @return int|float Result.
	 */
	private function execute_function( $function_name, $params ) {
		return call_user_func_array( $this->functions[ $function_name ]['func'], array_reverse( $params ) );
	}
}
