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
        var request_in_progress = false;
        var container = $('.shopstyle-frontend');

        $(window).bind('scroll', function() {
            if(!request_in_progress)
                scrollReaction();

            var trigger_height = $('#shopstyle-filter-wrap').height()+$('#header').height()+50;
             console.log('height =  '+trigger_height);
             console.log('scrollTop =  '+$(window).scrollTop());
            if ($(window).scrollTop() > trigger_height) {
                $('#shopstyle-filter-wrap').addClass("fix-sidebar animated slideInDown");
            } else {
                $('#shopstyle-filter-wrap').removeClass("fix-sidebar animated slideInDown");
            }
        });

        function scrollReaction() {
            var content_height = container.height();
            var current_y = window.innerHeight + window.pageYOffset;

            if(current_y >= content_height) {
                console.log('page='+$('#page-num').data('page'));
                console.log(request_in_progress);
                var total = $('#page-num').data('total');
                var products = 50*($('#page-num').data('page')+1);

                if(!request_in_progress && products < total)
                    loadMore();
            }
        }

        function loadMore() {

            if(request_in_progress) { return; }
            request_in_progress = true;
            console.log('running...');

            var page_num = parseInt($('#page-num').data('page'))+1;
            var api_token = $('#page-num').data('api');
            var keyword = $('#page-num').data('keyword');
            var cat = $('#page-num').data('cat');
            var discount = $('#page-num').data('discount');
            var min_price = priceSlider.noUiSlider.get()[0];
            var max_price = priceSlider.noUiSlider.get()[1];

            var colors = [];
            $.each($('#shopstyle-filter-wrap ul.filter-color li.active'), function (index) {
                colors.push($(this).data('color'));
            });
            colors = colors.join();

            var sizes = [];
            $.each($('#shopstyle-filter-wrap ul.filter-size li.active'), function (index) {
                sizes.push($(this).data('size'));
            });
            sizes = sizes.join();

            var brands = [];
            $.each($('#shopstyle-filter-wrap ul.filter-brand li.active'), function (index) {
                brands.push($(this).data('brand'));
            });
            brands = brands.join();

            var retailers = [];
            $.each($('#shopstyle-filter-wrap ul.filter-retailer li.active'), function (index) {
                retailers.push($(this).data('retailer'));
            });
            retailers =retailers.join();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajax_parms.ajaxurl,
                data: {
                    action: 'get_products_frontend',
                    api_token: api_token,
                    pg_number: page_num,
                    keyword: keyword,
                    cat: cat,
                    discount: discount,
                    min_price: min_price,
                    max_price: max_price,
                    color:colors,
                    size: sizes,
                    brand:brands,
                    retailer: retailers
                },
                beforeSend: function () {
                	$('#spinner').show();
                },
                success: (function (res) {
                    $('#spinner').hide();

                    if(res != null)
                        $('.shopstyle-frontend').append(res.html);

                    $('#page-num').data('page', page_num);
                    request_in_progress = false;
                })
            });
        }
//NoSlider Setting
        var priceSlider = document.getElementById('filter-price');

        if(priceSlider) {
            noUiSlider.create(priceSlider, {
                start: [0, 1000],
                snap: true,
                connect: true,
                range: {
                    'min': 0,
                    '2%': 20,
                    '4%': 40,
                    '6%': 60,
                    '8%': 80,
                    '10%': 100,
                    '15%': 150,
                    '20%': 200,
                    '25%': 250,
                    '30%': 300,
                    '40%': 400,
                    '50%': 500,
                    '60%': 600,
                    '70%': 700,
                    '80%': 800,
                    '90%': 900,
                    'max': 1000
                },
                format: wNumb({
                    decimals: 0,
                    prefix: '$'
                })
            });

            var snapValues = [
                document.getElementById('price-slider-value-min'),
                document.getElementById('price-slider-value-max')
            ];

            priceSlider.noUiSlider.on('update', function (values, handle) {
                snapValues[handle].innerHTML = values[handle];
            });

            priceSlider.noUiSlider.on('slide', function () {
                var min = parseInt(priceSlider.noUiSlider.get()[0].substr(1));
                var max = parseInt(priceSlider.noUiSlider.get()[1].substr(1));
                if (max == 1000) max = 100000;

                $.each($('.shopstyle-frontend .bs-component'), function (index) {
                    var price = $(this).data('price');
                    if (price >= min && price <= max) {
                        $(this).show(100);
                        return true;
                    }
                    $(this).hide(100);
                });

                console.log('Sliding...')
            });
        }

            //Widget filter Ajax
        $(document).on('click', '#shopstyle-filter-wrap ul.shopstyle-cats li,' +
            '#shopstyle-filter-wrap ul#filter-discount li',function (e) {

            e.preventDefault();

            if(request_in_progress) { return; }
                request_in_progress = true;

            if($(this).parents('ul.shopstyle-cats').hasClass('shopstyle-cats')){
                $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').removeClass('active');
                $(this).addClass('active');
            }

            if($(this).parents('ul#filter-discount').hasClass('level-2')){
               $('#shopstyle-filter-wrap ul#filter-discount li.active').removeClass('active');
               $(this).addClass('active');
            }

            var cat = $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').data('catid');
            if(cat=='')
                cat = $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').data('cat');

            var page_num = 0;
            var api_token = $('#page-num').data('api');
            var keyword = $('#search-product').val();
            $('#page-num').data('keyword');

            var discount = $('ul#filter-discount li.active').data('discount');

            var colors = [];
            $.each($('#shopstyle-filter-wrap ul.filter-color li.active'), function (index) {
                colors.push($(this).data('color'));
            });
            colors = colors.join();

            var sizes = [];
            $.each($('#shopstyle-filter-wrap ul.filter-size li.active'), function (index) {
                sizes.push($(this).data('size'));
            });
            sizes = sizes.join();

            var brands = [];
            $.each($('#shopstyle-filter-wrap ul.filter-brand li.active'), function (index) {
                brands.push($(this).data('brand'));
            });
            brands = brands.join();

            var retailers = [];
            $.each($('#shopstyle-filter-wrap ul.filter-retailer li.active'), function (index) {
                retailers.push($(this).data('retailer'));
            });
            retailers =retailers.join();

            $('#page-num').data('cat', cat); //update category for load more
            $('#page-num').data('discount', discount); //update discount for load more

            var min_price = priceSlider.noUiSlider.get()[0];
            var max_price = priceSlider.noUiSlider.get()[1];

            if(min_price=="$0")
                min_price = '';
            else
                $('#page-num').data('min-price', min_price); //update min price for load more

            if(max_price=="$1000")
                max_price = '';
            else
                $('#page-num').data('max-price', max_price); //update max price for load more

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajax_parms.ajaxurl,
                data: {
                    action: 'get_products_frontend',
                    api_token: api_token,
                    pg_number: page_num,
                    keyword: keyword,
                    cat: cat,
                    discount: discount,
                    min_price: min_price,
                    max_price: max_price,
                    color: colors,
                    size: sizes,
                    brand: brands,
                    retailer: retailers,
                },
                beforeSend: function () {
                    $('.bs-component').slideDown(500).remove();
                    $('#spinner').show();
                },
                success: (function (res) {
                    $('#spinner').hide();
                    $('#page-num').data('page', 0);

                    request_in_progress = false;

                    if(res!=null) {
                        $('.shopstyle-frontend').append(res.html);
                        if (res.total)
                            $('#page-num').data('total', res.total);
                    }
                })
            });
        })

        //color filter
        $('#shopstyle-filter-wrap ul.filter-color li').on('click', function (e) {
            e.preventDefault();
            if($(this).hasClass('active'))
                $(this).removeClass('active');
            else
                $(this).addClass('active');

            $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').click();
        })

        //size filter
        $('#shopstyle-filter-wrap ul.filter-size li').on('click', function (e) {
            e.preventDefault();
            if($(this).hasClass('active'))
                $(this).removeClass('active');
            else
                $(this).addClass('active');

            $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').click();
        });

        //Search Keyword
        $('#shopstyle-filter-wrap #search-product').on('keyup', function (e) {
            if(e.keyCode==13)
                $('#shopstyle-filter-wrap ul.shopstyle-cats li.active').click();
        })

        html = '<a target="_blank" data-toggle="tooltip" data-placement="left" title="'+domain+'.'+"<?php echo $tld; ?>"+'"'
        +' href="https://www.whoishostingthis.com/go/whois/'+domain+"/?tag=pr1&track=WIHT-SiteProfile"+'">'
            +'<div class="col-lg-8 col-md-8 col-sm-7 col-xs-7 domain-name">'
        '<span class="InstantDomainShow">'+domain+'</span><span class="domain-ext">.'+"<?php echo $tld; ?>"+'</span></div>'
        +'<div class="col-lg-4 col-md-4 col-sm-5 col-xs-5 domain-button">'
        +'<div id="tld_whois" class="btn-dmn btn-red">WHO IS</div></div></a>'
  	});
})( jQuery );
