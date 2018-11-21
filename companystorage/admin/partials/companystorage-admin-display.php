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

?>

<div class="container-fluid companystorage-wrapper" style="width: 900px">
    <div class="wrap">
        <h1 style="text-align: left;">Admin Dashboard</h1>
        <hr/>
    </div>
    <?php

    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    }
    $upgrader = new Plugin_Upgrader();
    $button_color = (string)trim(get_option('company_main_button_color'));

    if($button_color==='')
        $button_color = '#200442';
    $response = $this->get_plugins_api('mailgun');
    $download_link = $response->download_link;

    $mailgun_status = $this->is_plugin_installed('mailgun');

    if(!$mailgun_status) {
        echo 'This plugin requires Mailgun plugin installed'.'<br/>';
        $response = $this->get_plugins_api('mailgun');

        if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }

        $upgrader = new Plugin_Upgrader();

        $res = $upgrader->install($response->download_link);
        activate_plugin('mailgun/mailgun.php');
        echo 'Activated Mailgun plugin'.'<br/>';
        echo 'Please setup mailgun'.'<br/><br/>';
        ?>
        <a class="btn btn-default" href="<?= admin_url().'/options-general.php?page=mailgun'?>">Go to Mailgun setting </a>
    <?php


//<!--    <form id="storage_mailgun_install_form" method="post">-->
//<!--        <h5>You need to install Mailgun plugin.  Please click the button to install Mailgun plugin</h5>-->
//<!--        <button type="submit" class="btn btn-default"><i class="ajax_loading"></i>Install Mailgun-->
//<!--        </button>-->
//<!--    </form>-->
//<!--        <div id="result_install"></div>-->
//<!---->

    }

    $email_lists = get_option('company_email_lists');

    //    print_r($plugins);
    if (empty($email_lists))
        $count_emails = 1;
    else
        $count_emails = count($email_lists);

    $this->company_javascript_block($count_emails);

    ?>
    <hr/>

    <form action="options.php" method="post">
        <?php settings_fields('company-setting-group'); ?>
        <?php do_settings_sections( 'company-setting-group');
        ?>
        <table class="form-table" id="company-emails"  data-lastindex="<?= $count_emails; ?>">
            <tr valign="middle">
                <th scope="row" >Company Name</th>
                <td>
                    <input name="company_company_name" required placeholder="Company Name"  value="<?= get_option('company_company_name') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Coord API key</th>
                <td>
                    <input name="company_coord_key" style="width: 400px" required placeholder="Coord key"  value="<?= get_option('company_coord_key') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Bing Truck mile API key</th>
                <td>
                    <input name="company_bing_key" style="width: 400px" required placeholder="Bing Truck mile API key"  value="<?= get_option('company_bing_key') ?>"/>
                </td>
            </tr>

            <tr valign="middle">
                <th scope="row" >Rate / Mile ($)</th>
                <td>
                    <input name="company_rate_per_mile" required placeholder="Rate Per Mile($)"  value="<?= get_option('company_rate_per_mile') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Minimum Delivery Charge ($) </th>
                <td>
                    <input name="company_flat_delivery_price" required placeholder="Minimum Delivery Charge($)"  value="<?= get_option('company_flat_delivery_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Local Radius (mi)</th>
                <td>
                    <input name="company_local_radius" required placeholder="Local Radius(mi)"  value="<?= get_option('company_local_radius') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Damage Waiver ($/month)</th>
                <td>
                    <input name="company_damage_waiver" required placeholder="Damage Waiver ($/month)"  value="<?= get_option('company_damage_waiver') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Contents Protection 5K ($/month)</th>
                <td>
                    <input name="company_contnets_protection" required placeholder="Contents Protection 5K ($/month)"  value="<?= get_option('company_contnets_protection') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Live Unload Wait Price ($)</th>
                <td>
                    <input name="company_wait_price" required placeholder="Live Unload Wait Price ($)"  value="<?= get_option('company_wait_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Live Unload Threshold Miles (mi)</th>
                <td>
                    <input name="company_threshold_miles" required placeholder="Live Unload Threshold Miles (mi)"  value="<?= get_option('company_threshold_miles') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Minimum Live Unload Save Price ($)</th>
                <td>
                    <input name="company_live_unload_save_price" required placeholder="Minimum Live Unload Save Price ($)"  value="<?= get_option('company_live_unload_save_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >16' Container Price ($)</th>
                <td>
                    <input name="company_16_container_price" required placeholder="16' Container Price ($)"  value="<?= get_option('company_16_container_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >20' Container Price ($)</th>
                <td>
                    <input name="company_20_container_price" required placeholder="20' Container Price ($)"  value="<?= get_option('company_20_container_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Long Distance Minimum threshold (mi)</th>
                <td>
                    <input name="company_long_distance_threshold" required placeholder="Long Distance Minimum threshold (mi)"  value="<?= get_option('company_long_distance_threshold') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Long Distance Mile Price ($)</th>
                <td>
                    <input name="company_long_distance_mile_price" required placeholder="Long Distance Mile Price ($)"  value="<?= get_option('company_long_distance_mile_price') ?>"/>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Signature</th>
                <td>
                    <?php wp_editor( get_option('company_signature'), 'company_signature', array('textarea_rows'=>3)); ?>
                </td>
            </tr>
            <tr valign="middle">
                <th scope="row" >Main Button Color</th>
                <td>
                    <input type="hidden" id="company_main_button_color" name="company_main_button_color" required placeholder="Main Button Color"  value="<?= $button_color ?>"/>
                    <div id="colorSelector">
                        <div style="background-color: <?= $button_color ?>;"></div>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" >Custom Message for Emailed Quote</th>
                <td>
                    <?php wp_editor( get_option('company_custom_message'), 'company_custom_message', array('textarea_rows'=>5)); ?>
                </td>
            </tr>
            <?php
            $index = 1;
            if(is_array($email_lists) && count($email_lists)>0) {
                foreach ($email_lists as $email){
                    $this->get_email_row($index, $email, count($email_lists));
                    $index++;
                }
            } else {
                $this->get_email_row($index);
            }
            ?>
        </table>
        <hr />
        <?php
        $index = 1;
        $warehouses_names = get_option('company_warehouse_names');
        $tolls = get_option('company_tolls_list');

        if (empty($warehouses_names))
            $count_warehouses = 1;
        else
            $count_warehouses = count($warehouses_names);
        ?>
        <table class="form-table" id="company_warehouses" data-last_warehouse="<?= $count_warehouses; ?>">
            <thead valign="bottom">
            <th scope="col"  width="120px">Warehouse Name</th>
            <th scope="col" width="320px">Warehouse Address</th>
            <th scope="col" width="80px">Indoor warehouse storage price</th>
            <th scope="col" width="80px">Outdoor warehouse storage price</th>
            <th></th>
            </thead>
            <tbody>
            <?php
            $index = 1;
            $warehouses_names = get_option('company_warehouse_names');
            $w_addresses = get_option('company_warehouse_addresses');
            $w_indoors = get_option('company_warehouse_indoor_prices');
            $w_outdoors = get_option('company_warehouse_outdoor_prices');
            $warehouses_counts = count($warehouses_names);

            if(is_array($warehouses_names) && count($warehouses_names)>0) {
                foreach ($warehouses_names as $name){
                    $this->get_warehouse_row($index, $name, $w_addresses[$index-1],$w_indoors[$index-1], $w_outdoors[$index-1],$warehouses_counts);
                    $index++;
                }
            } else {
                $this->get_warehouse_row($index);
            }
            ?>
            </tbody>
        </table>
        <hr/>
        <?php
        $index = 1;
        $toll_names = get_option('company_toll_names');
        $toll_amounts = get_option('company_toll_amounts');

        $toll_counts = count($toll_names);
        ?>
        <table class="form-table" id="company_tolls" data-last_toll="<?= $toll_counts; ?>">
            <thead valign="bottom">
            <th scope="col" width="360px">Toll</th>
            <th scope="col" width="120px">Amount</th>
            <th width="48px"></th>
            </thead>
            <tbody>

            <?php
            if(is_array($toll_names) && $toll_counts>0) {
                foreach ($toll_names as $name){
                    $this->get_toll_row($index, $name, $toll_amounts[$index-1], $toll_counts);
                    $index++;
                }
            } else {
                $this->get_toll_row($index);
            }
            ?>
            </tbody>
        </table>
        <hr/>

        <?php submit_button() ?>
    </form>
</div>
<script type="text/javascript">

    jQuery(document).ready(function ($) {
        $('#colorSelector').ColorPicker({
            color: '<?= $button_color ?>',
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                $('#colorSelector div').css('backgroundColor', '#' + hex);
                $('#company_main_button_color').val('#' + hex);
            }
        });
    });
</script>
