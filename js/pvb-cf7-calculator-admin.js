/**
 * File: pvb-cf7-calculator-admin.js
 *
 * Provides support functions for the admin dashboard.
 *
 * @since 1.0.0
 *
 * @package PVBCF7Calculator
 */

/**
 * Posts a message to the backend to enable a "powered by" badge.
 *
 * @param {string|jQuery} aElm    Target element for confirmation output.
 * @param {string}        msgWait Message to display while waiting
 *                                for the asynchronous action.
 * @param {string} msgOK          Confirmation message to display on success.
 */
function pvbCf7CalculatorPoweredByEnable(aElm, msgWait, msgOK) {
	jQuery( aElm ).text( msgWait );
	jQuery.post(
		ajaxurl,
		{action: 'pvb_powered_by_opt_in'},
		function() {
			jQuery( aElm ).text( msgOK );
		}
	);
}

/**
 * Hides admin notices and posts a message to the backend to not display them
 * (either temporarily or permanently).
 *
 * @param {number}        period The dismissal period, either 1 (temporary)
 *                               or any other value (permanent).
 * @param {string|jQuery} target Container element from which
 *                               notices should be removed.
 */
function pvbCf7CalculatorHideAdminNotices(period, target) {
	jQuery( target ).closest( '.notice' ).remove();

	if (period == 1) {
		// Dismiss temporarily.
		jQuery.post( ajaxurl, {action: 'pvb_promo_pause'} );
	} else {
		// Dismiss permanently.
		jQuery.post( ajaxurl, {action: 'pvb_promo_disable'} );
	}

}
