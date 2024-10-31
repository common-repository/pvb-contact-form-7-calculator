/**
 * File: pvb-cf7-calculator.js
 *
 * Provides front-end support functions.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

/**
 * Performs calculations on a form.
 *
 * @param {jQuery} $form The form to process.
 */
function cf7Calculate($form) {
	// Retrieve form ID.
	var form_id   = null;
	var $form_div = $form.closest( 'div.wpcf7' );
	if ( ! $form_div.length) {
		return;
	}
	var form_div_id = $form_div.attr( 'id' );
	var matches     = form_div_id.match( /wpcf7-f([0-9]+)/ );
	if (matches !== null) {
		form_id = matches[1];
	}
	if (form_id === null) {
		return;
	}

	var $button = $form_div.find( '.cf7-calculate_button' );

	// Retrieve form values.
	$form = $button.closest( 'form' );
	if ( ! $form.length) {
		return;
	}
	var postdata = $form.serializeArray();

	// Add form ID to posted data.
	postdata.push( {name: 'action', value: 'pvb_calculate'} );
	postdata.push( {name: 'pvb_form_id', value: form_id} );

	// Send data to server.
	jQuery.post(
		frontend_ajax_object.ajaxurl,
		postdata,
		function(r) {
			// Fill fields with results.
			if (r instanceof Object) {
				var nameLowercaseCompare = function() {
					return this.name.toLowerCase() === name.toLowerCase();
				};
				for (var name in r) {
					$form.find( 'input[name]' ).filter( nameLowercaseCompare ).val( r[name] );
				}
			}
			// Enable button again.
			if ($button) {
				$button.prop( 'disabled', false );
			}
			$form.trigger( 'wpcf7calculate' );
		}
	).fail(
		function() {
				// Enable button again.
				$button.prop( 'disabled', false );
				$form.trigger( 'wpcf7calculatefail' );
		}
	);
}

// Attach to Calculate buttons.
jQuery(
	function($) {
		jQuery( 'body' ).on(
			'click',
			'.cf7-calculate_button',
			function(event) {
				// Disable button.
				var $button = jQuery( event.target );
				if ( ! $button.length) {
					return;
				}
				$button.prop( 'disabled', true );
				cf7Calculate( $button.closest( 'form' ) );
			}
		);
	}
);
