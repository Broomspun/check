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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1 style="text-align: left;" class="col-sm-offset-2">MySQL Setting to see device history</h1>
</div>
<hr/>
<div class="container-fluid device-center">

    <form class="form-horizontal" type="POST" id="device-center-form">
        <div class="col-sm-offset-1 col-sm-8">
            <input type="hidden" name="device-center-import-nonce" id="device-center-import-nonce"
                   value="<?php echo wp_create_nonce('device-center-import-nonce'); ?>"/>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="device-center-mysql-server">MySQL Server<sup>*</sup></label>
                <div class="col-sm-3">
                    <input class="form-control" type="text" id="device-center-mysql-server"
                           name="device-center-mysql-server" required
                           placeholder="localhost" value="localhost">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="device-center-user">MySQL User<sup>*</sup></label>
                <div class="col-sm-3">
                    <input type="text" class="form-control"  id="device-center-user" name="device-center-user" required
                           placeholder="user">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="device-center-password">Password<sup>*</sup></label>
                <div class="col-sm-3">
                    <input class="form-control" type="password" id="device-center-password"
                           name="device-center-password" required
                           placeholder="" value="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="device-center-database">Database Name<sup>*</sup></label>
                <div class="col-sm-3">
                    <input class="form-control" type="text" id="device-center-database" name="device-center-database"
                           placeholder="" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="device-center-_submit"> </label>
                <div class="col-sm-1">
                    <button type="submit" id="device-center-_submit" class="btn btn-default"><i class="ajax_loading"></i>Save Setting</button>
                </div>
            </div>
        </div>
    </form>
</div>