<?php
/**
 * File: class-tokens.php
 *
 * Defines the Tokens interface.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

namespace PVBCF7Calculator\lib\Math;

interface Tokens {

	const PLUS  = '+';
	const MINUS = '-';
	const MULT  = '*';
	const DIV   = '/';
	const POW   = '^';
	const MOD   = '%';

	const ARG_SEPARATOR = ',';
	const FLOAT_POINT   = '.';

	const PAREN_LEFT  = '(';
	const PAREN_RIGHT = ')';

	const OPERATORS   = array( Tokens::PLUS, Tokens::MINUS, Tokens::MULT, Tokens::DIV, Tokens::POW, Tokens::MOD );
	const PARENTHESES = array( Tokens::PAREN_LEFT, Tokens::PAREN_RIGHT );
}
