=== PVB Contact Form 7 Calculator Add-on ===
Contributors: pbosakov
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LZBNZ48HNTY2A
Tags: calculator, cost calculator, price calculator, loan calculator, mortgage calculator, Contact Form 7, PayPal, Stripe
Requires at least: 4.9.2
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: trunk

Lets you easily turn any Contact Form 7 form into a quote or price estimate calculator.

== Description ==

#### Overview ####

With PVB Contact Form 7 Calculator, you can easily turn any Contact Form 7 form into a calculator. Calculated fields are based on user input and selections in other parts of the form. The plugin can be used for creating various types of calculators, such as an ideal weight calculator, calorie calculator, quote calculator for hotel booking, car rental quote calculator, mortgage calculator, tax calculator, finance calculators, date calculator, etc.

#### Features ####

* __Advanced calculations__ - set your custom formula based on user input, for example:
	`cost_per_day * days * (1 + (tax_percentage / 100))`
* __Hide calculated fields__ - they can be visible to the user or not. Your choice!
* __Server-side calculation__ - you don't have to disclose your top secret formula! Calculations are performed behind the scenes, on the server.
* __Calculate on button click__ - you can let your users trigger the calculations without submitting the form.
* __Calculate on submit__ - values are always calculated on submit, so you can use them in your notification emails.
* __Calculate on other events__ - you can also trigger calculations from another script at any time!
* __Correct floating-point math__ - PVB Contact Form 7 Calculator Pro handles decimal floating-point math properly, unlike many online calculators that introduce rounding errors when converting to binary and back, yielding incorrect results (such as 0.6 + 0.3 = 0.8999).
* __Multiple calculated fields__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - you can have as many as you like in the same form!
* __Intermediate calculation steps__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - calculated fields can be based on other calculated fields. They are evaluated in order, from top to bottom.
* __Assign numeric values to text fields__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - for example, you can assign a specific price to each choice in a drop-down menu.
* __PayPal and Stripe integration__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - accept payments from your website visitors based on the calculated amount
* __Currency conversion__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - use up-to-date currency exchange rates in your formulas.
* __Custom PHP code__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - the calculator is not advanced enough for you? Now you can run any PHP code in your calculation formulas. For security reasons, this feature is disabled by default and you must explicitly enable it from the Options page.
* __Reference external spreadsheets__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - need to update your price list frequently? The calculator can look up data from an online spreadsheet in various formats (Google Sheets, Excel, CSV...)
* __Calculate travel distance and time__ _([Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/) only)_ - integration with Google Maps API allows you to work with addresses and locations.

#### Live demo ####

We have prepared a [Live Demo](http://bossakov.eu/wpdemo/pvb-cf7-calculator-pro/) to let you test the capabilities of PVB Contact Form 7 Calculator.

#### Video tutorial ####

https://www.youtube.com/watch?v=1A2e60_DcZ4

#### Pro version ####

If you like PVB Contact Form 7 Calculator, please consider upgrading to our [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/). It lets you have multiple calculated fields per form, calculations based on text values (such as drop-down menus), integrate your calculator with PayPal, Stripe and Google Sheets, and other great features scheduled for future releases.

#### WP Admin Notices ####

This plugin generates an [Admin Notice](https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices) in the WP Admin dashboard. It will invite you to rate our plugin or consider upgrading to the [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/). These notices may annoy or confuse certain users, but are appreciated by the majority of our userbase, who understand that these notices support our free contributions to the WordPress community.

If you feel that these notices are too annoying, then we encourage you to consider our [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/), or even consider developing your own plugins for WordPress, if supporting free plugin authors is too frustrating for you. A final alternative would be to place the defined constant mentioned below inside of your `wp-config.php` file to manually hide this plugin's nag notices:

    define('DISABLE_NAG_NOTICES', true);

Note: This defined constant will only affect the notices mentioned above, and will not affect any other notices generated by this plugin, such as one-time notices that communicate with admin-level users.

== Installation ==

[Contact Form 7](https://wordpress.org/plugins/contact-form-7/) 5.0 or later is required.

1. Download the plugin zip file to your computer;
2. Unzip the file;
3. Upload the pvb-contact-form-7-calculator directory to your /wp-content/plugins/ directory.
4. Activate the plugin from the "Plugins" menu in WordPress.
5. Refer to the [plugin documentation](http://bossakov.eu/documentation/pvb-cf7-calculator-pro-1.4.0.pdf).

== Frequently asked questions ==

= Can I accept payments with PVB Contact Form 7 Calculator? =
Simple PayPal and Stripe integration is available in the [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/). The plugin can generate a Pay Now button on the fly when a calculation is made.

= Can I use PVB Contact Form 7 Calculator for currency conversion? =
Yes, you can! A currency conversion function is available in the [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/).

As an alternative, you can use the "wpcf7calculate" jQuery event to trigger currency conversion or other post-processing operations after a calculation. You can code your own Javascript function and place it somewhere on your website to call a third-party currency conversion API. Here is an example:

~~~~javascript
jQuery(function($) {
      $('form').on('wpcf7calculate', function() { 
        var dollars = jQuery('input[name=total]').val();
        var euro = currencyConvert(dollars, 'USD', 'EUR');
        if (euro === false) {
            alert('Currency conversion failed');
        } else {
            alert(dollars + ' dollars = ' + euro.toFixed(2) + ' euro');
        }
        return;
    });
});

function currencyConvert(amount, from, to) {
    var result = '';
    var convertUrl = "https://free.currencyconverterapi.com/api/v3/convert?q=" + from + "_" + to + "&compact=ultra";
    var rate = false;
    jQuery.ajax({
        url: convertUrl,
        async: false,
        type: "GET",
        dataType: "json",
        success: function (data) {
            rate = data[from + "_" + to];
        }
    });
    return rate ? parseFloat(amount) * parseFloat(rate) : false;
}
~~~~

= Can I use PVB Contact Form 7 Calculator to calculate distance and travel time? =
Yes, this is available in the [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/). This feature requires that you have a valid Google Places API key.

= Can I reference a list of values in a spreadsheet when configuring my calculation formula? =
This is available in the [Pro version](https://bossakov.eu/product/pvb-contact-form-7-calculator-pro/). Please refer to the [plugin documentation](http://bossakov.eu/documentation/pvb-cf7-calculator-pro-1.4.0.pdf) for more information about support for external spreadsheets.

= How does the calculator handle multiple checkbox groups? =
If more than one checkbox is selected in the same group, the values will be summed. To avoid the possibility to select more than one checkbox at the same time, use the "exclusive" modifier in the checkbox tag, or use radio buttons instead. More information and examples are available in the [plugin documentation](http://bossakov.eu/documentation/pvb-cf7-calculator-pro-1.4.1.pdf).

== Screenshots ==

1. Loan calculator
2. Booking calculator
3. Ideal weight calculator

== Changelog ==

= 1.0.11 =
* Fix: PHP 8 fatal error
* Fix: Calculate button shortcode issues
* Code formatting and documentation updates
* Tested for compatibility with WordPress 6.3

= 1.0.10 =
* Fix: error when adding negative numbers
* Fix: calculation field names with uppercase letters do not work properly
* Fix: WordPress warning related to add_submenu_page function parameters
* Fix: Calculate button not working with some themes

= 1.0.9 =
* Fix: fatal error when attempting to activate both free and premium versions of the plugin at the same time

= 1.0.8 =
* Fix: division bug

= 1.0.7 =
* Fix: multiple guidelines and security fixes

= 1.0.6 =
* Update: better user control over admin notifications

= 1.0.5 =
* Fix: incorrect handling of non-integer exponents

= 1.0.4 =
* Fix: avoid interfering with CF7 style sheets

= 1.0.3 =
* Fix: cf7-hide option not working with some themes

= 1.0.2 =
* Fix: rounding results to a whole number not working

= 1.0.1 =
* Fix: groups of multiple checkboxes not supported

= 1.0.0 =
* initial release