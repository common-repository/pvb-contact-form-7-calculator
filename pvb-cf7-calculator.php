<?php
/**
 * PVB Contact Form 7 Calculator
 *
 * @package PVBCF7Calculator
 * @author Petko Bossakov
 * @copyright 2018 Petko Bossakov
 * @license GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:  PVB Contact Form 7 Calculator
 * Plugin URI:   https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/
 * Description:  Lets you easily turn any Contact Form 7 form into a quote or price estimate calculator.
 * Version:      1.0.11
 * Author:       Petko Bossakov
 * Author URI:   http://bossakov.eu/
 * License:      GPLv3 or later
 * Text Domain:  pvb-cf7-calculator
 * Domain Path:  /languages
 */

namespace PVBCF7Calculator;

use PVBCF7Calculator\lib\PVBCF7Calculator;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access' );
}

// Do not proceed if the premium version of the plugin is active.
if ( ! defined( 'PVB_CF7_CALCULATOR_PRO_ACTIVE' ) ) {
	require_once 'autoload.php';
	spl_autoload_register( 'pvb_cf7_calculator_autoload' );
	$pvb_cf7_calculator = new PVBCF7Calculator();
}
