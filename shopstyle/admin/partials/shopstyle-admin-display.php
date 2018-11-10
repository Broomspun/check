<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */

echo '<div class="wrap">';
    echo '<h1 style="text-align: left;" class="col-sm-offset-2">ShopStyle API Setting & import Products</h1>';
    echo '<hr/>';
    echo '</div>';
?>
<div class="container-fluid shopstyle-products">

    <form class="form-horizontal" type="POST" id="shopstyle_import_form">
        <div class="col-sm-offset-1 col-sm-8">
            <input type="hidden" name="shopstyle_import_nonce" id="shopstyle_import_nonce"
                   value="<?php echo wp_create_nonce('shopstyle_setting_nonce'); ?>"/>
            <input type="hidden" name="pg_number" id="pg_number" value="0"/>
            <input type="hidden" name="total_products" id="total_products" value="0"/>
            <input type="hidden" name="total_pages" id="total_pages" value="0"/>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="shopstyle_api_token">API Token</label>
                <div class="col-sm-2">
                    <input class="form-control" type="text" id="shopstyle_api_token"
                           name="shopstyle_api_token" required
                           placeholder="your API Token" value="<?php echo $this->APIToken; ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="shopstyle_categories">Brand</label>
                <div class="col-sm-2">
                    <select class="form-control" type="text" id="shopstyle_brands" name="shopstyle_brands">
                        <option value="-1">Choose any Brand</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="shopstyle_categories">Category</label>
                <div class="col-sm-2">
                    <select class="form-control" type="text" id="shopstyle_categories"
                            name="shopstyle_categories">
                        <option value="-1">Choose any category</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="shopstyle_keywords">Keywords</label>
                <div class="col-sm-4">
                    <input class="form-control" type="text" id="shopstyle_keywords"
                           name="shopstyle_keywords" placeholder="Type keyword" value="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="shopstyle_ppp">Products per page(Max. 50)</label>
                <div class="col-sm-2">
                    <input class="form-control" type="text" id="shopstyle_ppp" name="shopstyle_ppp" required
                           placeholder="Pages per page" value="20">
                </div>
            </div>
            <hr/>
            <button type="submit" class="col-sm-offset-2 btn btn-default"><i class="ajax_loading"></i>Import
                Products
            </button>
            <a id="shopstyle-products-csv-file" class="btn btn-default" href="<?php echo plugin_dir_url( __FILE__ ) ."../../public/products.csv";?>" style="display: none; text-decoration: none;">Download products CSV </a>
        </div>
        <div class="clearfix"></div>
        <hr/>
        <div class="tablenav top hidden">

            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk
                    action</label><select name="action" id="bulk-action-selector-top">
                    <option value="-1">Bulk Actions</option>
                    <option value="import">Post to Woocommerce</option>
                </select>
                <button type="submit" id="doaction" class="button action" value="Apply"><i
                            class="ajax_loading1"></i><span class="button_title">Apply</span></button>
            </div>
            <div class="tablenav-pages"><span class="displaying-num">1,000 items</span>
                <span class="pagination-links">
                            <a class="first-page" href="#">
                                <span class="screen-reader-text">First page</span>
                                <span aria-hidden="true">«</span>
                            </a>
                            <a class="prev-page" href="#">
                                <span class="screen-reader-text">Previous page</span>
                                <span aria-hidden="true">‹</span>
                            </a>
                            <span class="first_sign tablenav-pages-navspan" aria-hidden="true">«</span>
                            <span class="prev_sign tablenav-pages-navspan" aria-hidden="true">‹</span>
                         <span class="paging-input">
                            <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                            <input class="current-page" id="current-page-selector" type="text" name="paged" value="1"
                                   size="3">
                             <span class="tablenav-paging-text"> of <span class="total-pages">50</span></span>
                         </span>
                        <a class="next-page" href="#">
                            <span class="screen-reader-text">Next page </span>
                            <span aria-hidden="true">›</span>
                        </a>
                        <a class="last-page" href="#">
                            <span class="screen-reader-text">Last page</span>
                            <span aria-hidden="true">»</span>
                        </a>
                            <span class="next_sign tablenav-pages-navspan" aria-hidden="true">›</span>
                            <span class="last_sign tablenav-pages-navspan" aria-hidden="true">»</span>
                        </span>
            </div>
            <br class="clear">
        </div>

        <div id="result" class="row">

        </div>
    </form>
</div>

