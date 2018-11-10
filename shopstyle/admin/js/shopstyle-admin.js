(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write $ code here, so the
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
    function processResult(response) {
        $('button i.ajax_loading').removeClass('fa fa-refresh fa-spin fa-fw');

        $('#total_products').val(response.totals);
        $('.tablenav.top').removeClass('hidden').addClass('show');
        $('span.displaying-num').html(response.totals+' items');

        var page_number = parseInt($('#pg_number').val())+1;

        $('.current-page').val(page_number);
        $('.total-pages').html(response.pages);
        $('#total_pages').val(response.pages);

        if(page_number>1){
            $('.first_sign').hide();
            $('.prev_sign').hide();
            $('.first-page').show();
            $('.prev-page').show();
        }
        else {
            $('.first_sign').show();
            $('.prev_sign').show();
            $('.first-page').hide();
            $('.prev-page').hide();
        }

        if(page_number==response.pages){
            $('.next-page').hide();
            $('.last-page').hide();
            $('.last_sign').show();
            $('.next_sign').show();
        }
        else {
            $('.next-page').show();
            $('.last-page').show();
            $('.last_sign').hide();
            $('.next_sign').hide();
        }


        $('#shopstyle_import_form #result').html('');
        var $html ='';
        $html += '<table class="table table-bordered"><thead><tr class="success"><th ><input id="cb-select-all-1" type="checkbox"><span style="margin-left: 10px">Product</span></th><th>Information</th><th>Price($)</th><th>Sizes</th><th>Category</th><th>Gallery</th></tr></thead>';
        $.each(response.product, function(key, value){
            var $temp = '';
            // var $single_uploading_btn='<p><button type="submit" class="single_product_uploading btn btn-default"><i class="ajax_loading_single"></i>Upload</button></p>';
            var $single_uploading_btn='';//

            var $images = '';
            if(value.small_gallery!==undefined) {
                $.each(value.small_gallery, function (index, image) {
                    $images += '<img style="padding: 5px; height: 100px;" src="' + image + '" />';
                });
            }

            var $colors = '';
            if(value.color!==undefined) {
                if (value.color.name !== undefined) {
                    $.each(value.color.name, function (index, colorname) {
                        $colors += '<div class="color-name" style="float: left;"><span>' + colorname + '</span>';
                        if (value.color.image[index] != null)
                            $colors += '<img style="height: 60px;" src="' + value.color.image[index] + '" />';
                        $colors += '</div>';
                    });
                }
            }

            var $sizes = '';
            if(value.size!==undefined) {
                if (value.size.name !== undefined) {
                    $.each(value.size.name, function (index, sizename) {
                        $sizes += '<p><strong><em>' + sizename + '</em></strong><br/>' + value.size.canonicalSize[index] + '</p>';

                    });
                }
            }
            var $retailers='';
            if(value.retailer!==undefined){
                $retailers += '<sp><span>id:'+value.retailer.id+'</span>&nbsp;&nbsp;';
                $retailers += '<span>name:'+value.retailer.name+'</span>&nbsp;&nbsp;';
                $retailers += '<span>score:'+value.retailer.score+'</span></p>';
            }

            $html += '<tr>'
                +'<td class="col-sm-1 center-block "><input type="checkbox" class="ss_ckb" name="ss_gi[]" value="'+key+'"><img class="center-block img-responsive" src="'+value['thumbnail']+'"/></td>'
                +'<td class="col-sm-6">'
                +'<p><strong><em>Product ID: </em></strong>'+key+'</p>'
                +'<p><Strong><em>Title: </em></Strong>'+value['title']+'</p>'
                +'<p>'+'<strong><em>Description: </em></strong>'+value['shortdescription']+'</p>'
                +'<p><strong><em>External Link: </em></strong><a target="_blank" href="'+value.link+'">'+value['link']+'</a></p>'
                +'<p><Strong><em>Sku: </em></Strong>'+value['sku']+'</p>'
                +'<p><Strong><em>Retailer: </em></Strong>'+$retailers+'</p>'
                +'<p><Strong><em>Colors: </em></Strong>'+$colors+'</p>'
                +$single_uploading_btn+'</td>'
                +'<td>'+value['price']+'</td>'
                +'<td style="width:80px;">'+$sizes+'</td>'
                +'<td>'+'<p>'+value['categories_names']+'</p></td>'
                +'<td>'+$images+'</td></tr>';
        });
        $html += '</table>';
        $('#shopstyle_import_form #result').append($html);
    }

    $(document).on('click', '#shopstyle_import_form #cb-select-all-1', function () {
        if($(this).is(":checked")) {
            $("#shopstyle_import_form .ss_ckb").each(function () {
                $(this).prop('checked', true)
            })
        }
        else {
            $("#shopstyle_import_form .ss_ckb").each(function () {
                $(this).prop('checked', false)
            })
        }
    });

    $(document).on('click', '#shopstyle_import_form #doaction',function (e) {
        e.preventDefault();
        var count = 0;
        var $items=[];
        $("#shopstyle_import_form .ss_ckb").each(function () {
            if($(this).is(":checked")){
                var $value = $(this).val();
                $items.push($value);
                count++;
            }
        });
        if(count==0){
            alert('Please select products to import!');
            return false;
        }
        if($('#shopstyle_import_form #bulk-action-selector-top').val()==-1){
            alert('Please select right option!');
            return false;
        }

        //  uploding products
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_parms.ajaxurl,
            data: {
                action: 'shopstyle_upload_products',
                items: $items,
            },
            beforeSend: function () {
                $('button#doaction i.ajax_loading1').addClass('fa fa-spinner fa-pulse fa-fw');
                $('button#doaction span.button_title').text('Uploading...');

            },
            success: (function (response) {
                $('button#doaction i.ajax_loading1').removeClass('fa fa-spinner fa-pulse fa-fw');
                $('button#doaction span.button_title').text('Apply');
                alert(response.message);
            })

        });

    });

    $("form#shopstyle_import_form").submit(function (e) {
        e.preventDefault();
        $('#pg_number').val(0);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_parms.ajaxurl,
            data: {
                action: 'shopstyle_import_products_ajax_request',
                shopstyle_import_nonce: $('#shopstyle_import_nonce').val(),
                shopstyle_api_token: $('#shopstyle_api_token').val(),
                shopstyle_ppp: $('#shopstyle_ppp').val(),
                pg_number: $('#pg_number').val(),
                shopstyle_keywords: $('#shopstyle_keywords').val(),
                category: $('#shopstyle_categories').val()
            },
            beforeSend: function () {
                $('button i.ajax_loading').addClass('fa fa-refresh fa-spin fa-fw');
            },
            success: (function (res) {
                $('button i.ajax_loading').removeClass('fa fa-refresh fa-spin fa-fw');
                processResult(res);
                $('#shopstyle-products-csv-file').fadeIn(1000);
            })

        });

    });


    //  get all categories
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajax_parms.ajaxurl,
        data: {
            action: 'get_shopstyle_categories',
        },
        beforeSend: function () {
        },
        success: (function (res) {
            $('#shopstyle_categories').append(res.html);
        })

    });

    $(document).on('click', '#shopstyle_import_form a.next-page,#shopstyle_import_form a.prev-page,#shopstyle_import_form a.first-page,#shopstyle_import_form a.last-page',function (e) {
        e.preventDefault();
        var page_number;

        if(this.className=='next-page')
            page_number = parseInt($('#shopstyle_import_form #pg_number').val())+1;
        else if(this.className=='prev-page')
            page_number = parseInt($('#shopstyle_import_form #pg_number').val())-1;
        else if(this.className=='first-page')
            page_number = 0;
        else if(this.className=='last-page')
            page_number = parseInt($('#shopstyle_import_form #total_pages').val())-1;


        $('#shopstyle_import_form #pg_number').val(page_number);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_parms.ajaxurl,
            data: {
                action: 'shopstyle_import_products_ajax_request',
                shopstyle_import_nonce: jQuery('#shopstyle_import_nonce').val(),
                shopstyle_api_token: jQuery('#shopstyle_api_token').val(),
                shopstyle_ppp: jQuery('#shopstyle_ppp').val(),
                pg_number: page_number,
                shopstyle_keywords: jQuery('#shopstyle_keywords').val(),
                category: jQuery('#shopstyle_categories').val()
            },
            beforeSend: function () {
                $('button i.ajax_loading').addClass('fa fa-refresh fa-spin fa-fw');
            },
            success: (function (response) {
                processResult(response,1);
            })

        });
    });

    //Chosen
    $('.shopstyle-cats-chosen').chosen();

});
})( jQuery );
