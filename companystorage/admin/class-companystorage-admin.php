<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class CompanyStorage_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    public $strings = array();
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    public function get_plugins( $plugin_folder = '' ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return get_plugins( $plugin_folder );
    }

    protected function get_plugins_api( $slug ) {
        static $api = array(); // Cache received responses.

        if ( ! isset( $api[ $slug ] ) ) {
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }

            $response = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );

            $api[ $slug ] = false;

            if ( is_wp_error( $response ) ) {
                wp_die( esc_html( $this->strings['oops'] ) );
            } else {
                $api[ $slug ] = $response;
            }
        }

        return $api[ $slug ];
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_register_style('font_awesome_css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('font_awesome_css');

        wp_enqueue_style( 'color-picker-css', plugin_dir_url( __FILE__ ) . 'css/colorpicker.css', array(), $this->version, 'all' );
//        wp_enqueue_style( 'color-picker-layout-css', plugin_dir_url( __FILE__ ) . 'css/layout.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/companystorage-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script( 'color-picker-js', plugin_dir_url( __FILE__ ) . 'js/colorpicker.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/companystorage-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_parms', array('ajaxurl' => admin_url('admin-ajax.php')));

    }

    public function is_plugin_installed( $slug ) {
        $installed_plugins = $this->get_plugins(); // Retrieve a list of all installed plugins (WP cached).

        return !empty( $installed_plugins[ $slug.'/'.$slug.'.php']);
    }

    public function getInstalledPlugin() {
        return $this->get_plugins(); // Retrieve a list of all installed plugins (WP cached).
    }

    public function install_mailgun_plugin() {

        $response = $this->get_plugins_api('mailgun');

        if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }

        $upgrader = new Plugin_Upgrader();
        ob_start();

        $res = $upgrader->install($response->download_link);

        $out1 = ob_get_contents();
        $data['result'] = $out1;
        wp_send_json(array('result'=>$out1, 'stauts'=>$res));
    }

    public function company_post_type(){
        $company_args = array(
            'labels'      => array(
                'name'          => __( 'Companies', 'companystorage' ),
                'singular_name' => __( 'Company', 'companystorage' ),
                'add_new_item ' => __( 'Add new company', 'companystorage' ),
                'edit_item ' => __( 'Edit company', 'companystorage' ),
                'all_items ' => __( 'All companies', 'companystorage' ),
            ),
            'public'      => true,
            'menu_icon'   => 'dashicons-megaphone',
            'has_archive' => false,
            'show_ui'     => true,
            'supports'    => array(
                'title',
            )
        );

        $offer_args['rewrite'] = array( 'slug' => 'companies' );

        register_post_type( 'company', $company_args );
    }

    public function companystorage_options()
    {
        register_setting('company-setting-group', 'company_company_name'); //Comapny Name
        register_setting('company-setting-group', 'company_coord_key'); //Comapny Name
        register_setting('company-setting-group', 'company_rate_per_mile'); //rate per mile
        register_setting('company-setting-group', 'company_flat_delivery_price'); //minimum delivery charge
        register_setting('company-setting-group', 'company_local_radius'); //local radius
        register_setting('company-setting-group', 'company_wait_price'); //live unload wait price
        register_setting('company-setting-group', 'company_threshold_miles'); //live unload threshold miles
        register_setting('company-setting-group', 'company_live_unload_save_price'); //live unload save price
        register_setting('company-setting-group', 'company_16_container_price'); //16' container price
        register_setting('company-setting-group', 'company_20_container_price'); //20' container price
        register_setting('company-setting-group', 'company_long_distance_threshold'); //Long distance threshold
        register_setting('company-setting-group', 'company_long_distance_mile_price'); //Long distance mile price
        register_setting('company-setting-group', 'company_email_lists'); //email Lists
        register_setting('company-setting-group', 'company_damage_waiver'); //email Lists
        register_setting('company-setting-group', 'company_contnets_protection'); //email Lists

        register_setting('company-setting-group', 'company_rd_email_lists'); //google or bing/tollguru
        register_setting('company-setting-group', 'company_warehouse_names'); //google or bing/tollguru
        register_setting('company-setting-group', 'company_warehouse_addresses'); //google or bing/tollguru
        register_setting('company-setting-group', 'company_warehouse_indoor_prices'); //google or bing/tollguru
        register_setting('company-setting-group', 'company_warehouse_outdoor_prices'); //google or bing/tollguru

        register_setting('company-setting-group', 'company_toll_names'); //toll Addressed
        register_setting('company-setting-group', 'company_toll_amounts'); //toll amount
        register_setting('company-setting-group', 'company_custom_message'); //custom message
        register_setting('company-setting-group', 'company_api_base_url'); //custom message
        register_setting('company-setting-group', 'company_bing_key'); //custom message
        register_setting('company-setting-group', 'company_main_button_color'); //custom message
        register_setting('company-setting-group', 'company_signature'); //custom message
    }


    public function company_storage_admin_menu(){
        add_menu_page('CompanyStorage Settings', 'CompanyStorage Settings', 'administrator', 'companystorage_setting', array($this, 'companystorage_setting'));
        add_action('admin_init', array($this,'companystorage_options') );
    }

    public function companystorage_setting(){
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require_once ('partials/companystorage-admin-display.php');
    }

    private function get_warehouse_row($index, $w_name='', $w_address='',$w_indoor=0, $w_outdoor=0,$row_count=1){
        ?>
        <tr valign="middle" id="warehouse_index_<?= $index?>">
            <td>
                <input style="width: 120px" name="company_warehouse_names[<?= $index-1 ?>]" required placeholder="Name"  value="<?= $w_name ?>"/>
            </td>
            <td>
                <input style="width: 320px" name="company_warehouse_addresses[<?= $index-1 ?>]" required placeholder="Address"  value="<?= $w_address ?>"/>
            </td>
            <td>
                <input style="width: 120px" name="company_warehouse_indoor_prices[<?= $index-1 ?>]" required placeholder="Indoor Price"  value="<?= $w_indoor ?>"/>
            </td>

            <td>
                <input style="width: 120px" name="company_warehouse_outdoor_prices[<?= $index-1 ?>]" required placeholder="Outdoor Price"  value="<?= $w_outdoor ?>"/>
            </td>
            <td width="48">
                <a class="add_warehouse" data-index="<?php echo $index; ?>">
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>"
                            title="<?php _e( 'add another warehouse', 'CompanyStorage'); ?>" alt="<?php _e( 'add another warehouse', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
                <a <?php echo ( $row_count > 1 ) ? '' : 'style="display:none;"'; ?> class="delete_warehouse" data-index="<?php echo $index; ?>" >
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>"
                            title="<?php _e( 'remove warehouse', 'CompanyStorage'); ?>" alt="<?php _e( 'remove warehouse', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
            </td>
        </tr>
        <?php
    }

    private function get_toll_row($index, $tollName='', $tollPrice='', $row_count=1) {
        ?>
        <tr valign="middle" id="toll_index_<?= $index?>">
            <td>
                <input style="width: 360px" name="company_toll_names[<?= $index-1 ?>]" required placeholder="Name"  value="<?= $tollName ?>"/>
            </td>
            <td>
                <input style="width: 120px" name="company_toll_amounts[<?= $index-1 ?>]" required placeholder="Amounts"  value="<?= $tollPrice ?>"/>
            </td>

            <td width="48">
                <a class="add_toll" data-index="<?php echo $index; ?>">
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>"
                            title="<?php _e( 'add another toll', 'CompanyStorage'); ?>" alt="<?php _e( 'add another toll', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
                <a <?php echo ( $row_count > 1 ) ? '' : 'style="display:none;"'; ?> class="delete_toll" data-index="<?php echo $index; ?>" >
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>"
                            title="<?php _e( 'remove toll', 'CompanyStorage'); ?>" alt="<?php _e( 'remove toll', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
            </td>
        </tr>
        <?php
    }

    private function get_email_row($index, $email='', $row_count=1) {
        ?>
        <tr valign="middle" id="email_index_<?= $index ?>">
            <th scope="row" >Admin User Email <?= $index ?></th>
            <td>
                <input type="email"  name="company_email_lists[<?= $index-1 ?>]" required placeholder="Email"  value="<?= $email ?>"/>
                <a class="add_admin_email" data-index="<?php echo $index; ?>">
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>"
                            title="<?php _e( 'add admin email', 'CompanyStorage'); ?>" alt="<?php _e( 'add admin email', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
                <a <?php echo ( $row_count > 1 ) ? '' : 'style="display:none;"'; ?> class="delete_admin_email" data-index="<?php echo $index; ?>" >
                    <img
                            src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>"
                            title="<?php _e( 'remove admin email', 'CompanyStorage'); ?>" alt="<?php _e( 'admin email', 'CompanyStorage'); ?>"
                            style="cursor:pointer; margin:0 3px;"/>
                </a>
            </td>
        </tr>
        <?php
    }

    private function company_javascript_block($email_counts) {
        ?>
        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                //Add Warehouse
                $('#company_warehouses').delegate('.add_warehouse', 'click', function (event) {
                    event.preventDefault();
                    adddWarehouse($(this).data('index'));
                });

                //Remove Warehouse
                $('#company_warehouses').delegate('.delete_warehouse', 'click', function (event) {
                    event.preventDefault();
                    deleteWarehoue($(this).data('index'));
                    return false;
                });


                //Add email
                $('#company-emails').delegate('.add_admin_email', 'click', function (event) {
                    event.preventDefault();
                    addEmail();
                });


                //Remove email
                $('#company-emails').delegate('.delete_admin_email', 'click', function (event) {
                    event.preventDefault();
                    deleteEmail($(this).data('index'));
                    return false;
                });

                //Add toll
                $('#company_tolls').delegate('.add_toll', 'click', function (event) {
                    event.preventDefault();
                    addToll($(this).data('index'));
                });

                //Remove a toll
                $('#company_tolls').delegate('.delete_toll', 'click', function (event) {
                    event.preventDefault();
                    deleteToll($(this).data('index'));
                    return false;
                });

                function adddWarehouse() {
                    var $index = $("#company_warehouses").data('last_warehouse')+1;
                    var currentIndex = $("#company_warehouses").data('last_warehouse');
                    console.log($index);
                    $("#company_warehouses").data('last_warehouse', $index);

                    var html = '';
                    html += '<tr valign="middle" id="warehouse_index_' + $index +'">';
                    html += '<td>';
                    html += '<input style="width: 120px" name="company_warehouse_names[' + currentIndex +']" required placeholder="Name"  value=""/>';
                    html += '</td><td>';
                    html += '<input style="width: 320px" name="company_warehouse_addresses[' + currentIndex + ']" required placeholder="Address"  value=""/>';
                    html += '</td><td>';
                    html += '<input style="width: 120px" name="company_warehouse_indoor_prices[' + currentIndex + ']" required placeholder="Indoor Price"  value=""/>';
                    html += '</td><td>';
                    html += '<input style="width: 120px" name="company_warehouse_outdoor_prices[' + currentIndex + ']" required placeholder="Outdoor Price"  value=""/>';
                    html += '</td><td width="48">';
                    html += '<a class="add_warehouse" data-index="' +currentIndex +'">';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>" title="<?php _e( 'add another warehouse', 'CompanyStorage'); ?>" alt="<?php _e( 'add another warehouse', 'CompanyStorage'); ?>" style="cursor:pointer; margin:0 3px;"/>';
                    html += '</a>';
                    html += '<a class="delete_warehouse" data-index="'+ $index + '" >';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>"  title="<?php _e( 'remove warehouse', 'CompanyStorage'); ?>" alt="<?php _e( 'remove warehouse', 'CompanyStorage'); ?>" style="cursor:pointer; margin:0 3px;"/>';
                    html += '</a></td></tr>';

                    $('#company_warehouses').append(html);
                    $('.delete_warehouse').show();
                }


                function deleteWarehoue(index) {
                    var $index = $("#company_warehouses").data('last_warehouse')-1;
                    $('tr#warehouse_index_'+index).remove();

                    if($index==1)
                        $('.delete_warehouse').hide();

                    $("#company_warehouses").data('last_warehouse', $index);
                }

                function addEmail(previousRowindex) {
                    var $index = $("#company-emails").data('lastindex')+1;
                    var currentIndex = $("#company-emails").data('lastindex');
                    $("#company-emails").data('lastindex', $index);
                    console.log('add email',$index);
                    var html = '';
                    html += '<tr valign="middle" id="email_index_'+$index+'">';
                    html +='<th scope="row" >Admin User Email '+parseInt($index)+'</th>'
                    html += '<td>';

                    html += '<input type="email"  name="company_email_lists['+currentIndex+']" required placeholder="Email"  value=""/>';
                    html += '<a class="add_admin_email" data-index="'+$index+'">';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>" title="<?php _e( 'add admin email', 'CompanyStorage'); ?>" alt="<?php _e( 'add admin email', 'CompanyStorage'); ?>"  style="cursor:pointer; margin:0 3px;"/>';
                    html += '</a>';
                    html += '<a class="delete_admin_email" data-index="'+ $index+'" >';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>" ';
                    html += 'title="<?php _e( 'remove admin email', 'CompanyStorage'); ?>" alt="<?php _e( 'admin email', 'CompanyStorage'); ?>"';
                    html += 'style="cursor:pointer; margin:0 3px;"/></a></td></tr>';

                    // $('#email_index_'+previousRowindex).after(html);
                    $('#company-emails').append(html);
                    $('.delete_admin_email').show();
                }

                /**
                 * @param previousRowindex:  Current index
                 */
                function deleteEmail(previousRowindex) {
                    var $index = $("#company-emails").data('lastindex')-1;
                    $('tr#email_index_'+previousRowindex).remove();

                    if($index==1)
                        $('.delete_admin_email').hide();

                    $("#company-emails").data('lastindex', $index);
                }

                function addToll() {
                    var $index = $("#company_tolls").data('last_toll')+1;
                    var currentIndex = $("#company_tolls").data('last_toll');
                    console.log($index);
                    $("#company_tolls").data('last_toll', $index);

                    var html = '';
                    html += '<tr valign="middle" id="toll_index_' + $index +'">';
                    html += '<td>';
                    html += '<input style="width: 360px" name="company_toll_names[' + currentIndex +']" required placeholder="Name"  value=""/>';
                    html += '</td><td>';
                    html += '<input style="width: 120px" name="company_toll_amounts[' + currentIndex + ']" required placeholder="Amounts"  value=""/>';
                    html += '</td><td style="width: 48px">';
                    html += '<a class="add_toll" data-index="' +currentIndex +'">';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/add.png'; ?>" title="<?php _e( 'add another toll', 'CompanyStorage'); ?>" alt="<?php _e( 'add another toll', 'CompanyStorage'); ?>" style="cursor:pointer; margin:0 3px;"/>';
                    html += '</a>';
                    html += '<a class="delete_toll" data-index="'+ $index + '" >';
                    html += '<img src="<?php echo company_plugin_url() . '/assets/images/remove.png'; ?>"  title="<?php _e( 'remove warehouse', 'CompanyStorage'); ?>" alt="<?php _e( 'remove toll', 'CompanyStorage'); ?>" style="cursor:pointer; margin:0 3px;"/>';
                    html += '</a></td></tr>';

                    $('#company_tolls').append(html);
                    $('.delete_toll').show();
                }

                function deleteToll(previousRowindex) {
                    var $index = $("#company_tolls").data('last_toll')-1;
                    $('tr#toll_index_'+previousRowindex).remove();

                    if($index==1)
                        $('.delete_toll').hide();

                    $("#company_tolls").data('last_toll', $index);
                }
            });
        </script>
        <?php
    }
}
