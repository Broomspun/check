(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $(function() {

        $("form#device-center-form").submit(function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajax_parms.ajaxurl,
                data: {
                    action: 'save_setting',
                    device_center_nonce: $('#device-center-import-nonce').val(),
                    host: $('#device-center-mysql-server').val(),
                    user: $('#device-center-user').val(),
                    dbname: $('#device-center-database').val(),
                    pass: $('#device-center-password').val(),
                },
                beforeSend: function () {
                    $('button i.ajax_loading').addClass('fa fa-refresh fa-spin fa-fw');
                },
                success: (function (res) {
                    $('button i.ajax_loading').removeClass('fa fa-refresh fa-spin fa-fw');
                })

            });



		});

    });

})( jQuery );
