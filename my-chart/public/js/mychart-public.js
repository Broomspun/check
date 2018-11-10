(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

     var timeFormat = 'YYYY/MM/DD HH:mm:ss';

     function newDate(days) {
         return moment(days).format("MMM Do YY");
     }

 	$('.all-graden-devices-list li a.garden-device').on('click', function (e) {
 		e.preventDefault();

 		var $device_id = $(this).data('device-id');
 		var $limit = $(this).data('limit');
 		var $width = $(this).data('width');
 		var $height = $(this).data('height');
        var $title = $(this).data('title');
        var $multiple = $(this).data('multiple');
        var $graph_title = $(this).data('graph_title');
        var $sensor_id = $(this).data('sensor_id');
        var $tick_width = $(this).data('tick_width');
        var $line_width = $(this).data('line_width');
        var $fill = $(this).data('fill');
        var $full_chart = $(this).data('full_chart');
        var $showline = $(this).data('showline');
        var $pointradius = $(this).data('pointradius');
        var $backgroundcolor = $(this).data('backgroundcolor');
        var $x_showgrid = $(this).data('x_showgrid');
        var $y_showgrid = $(this).data('y_showgrid');
        var $chart_type = $(this).data('chart_type');
        var $canvas_width = $(this).data('canvas_width');

        var $target = $('#my-chart'+$device_id+'-target');

 		console.log('clicked');
 		console.log('id='+$device_id);
 		console.log('limit='+$limit);

 		var $this = $(this).find('i.ajax-spin-loading');

 		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_parms.ajaxurl,
			data: {
				action: 'get_history_all_sensors',
				device_id: $device_id,
                sensor_id :$sensor_id,
				limit: $limit,
				width: $width,
				height: $height,
                title: $title,
        		multiple: $multiple,
        		graph_title: $graph_title,
        		tick_width: $tick_width,
        		line_width: $line_width,
        		fill: $fill,
        		full_chart: $full_chart,
        		showline: $showline,
        		pointradius: $pointradius,
        		backgroundcolor: $backgroundcolor,
        		x_showgrid: $x_showgrid,
        		y_showgrid: $y_showgrid,
        		chart_type: $chart_type,
                canvas_width: $canvas_width,
			},
            beforeSend: function () {
                $this.addClass('fa fa-refresh fa-spin fa-fw');
            },
            success: (function (res) {
                $this.removeClass('fa fa-refresh fa-spin fa-fw');
                $target.html('');
                $target.html(res.html);


                console.log('device_id='+res.device_id);
                console.log('all sensors='+res.sensors);
            })
		})
    })

   });

})( jQuery );
