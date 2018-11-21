<?php
/**
 * Created by PhpStorm.
 * User: yaksh
 * Date: 8/31/2018
 * Time: 1:08 AM
 */

class companyRestServer
{
    //The namespace and version for the REST SERVER
    var $my_namespace = 'company_rest_server/v';
    var $my_version   = '1';

    public function register_routes() {
        $namespace = $this->my_namespace . $this->my_version;

        register_rest_route( $namespace, '/settings' , array(
                array(
                    'methods'         => WP_REST_Server::READABLE,
                    'callback'        => array( $this, 'get_company_setting' ),
//                'permission_callback'   => array( $this, 'get_latest_post_permission' )
                )
            )
        );

        register_rest_route( $namespace, '/request_emails' , array(
                array(
                    'methods'         => WP_REST_Server::READABLE,
                    'callback'        => array( $this, 'request_get'),
                ),
                array(
                    'methods'         => WP_REST_Server::CREATABLE,
                    'callback'        => array( $this, 'request_quote'),
                ),
            )
        );
    }

    // Register our REST Server
    public function hook_rest_server(){
        add_action( 'rest_api_init', array( $this, 'my_customize_rest_cors' ) );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function get_company_setting(){
        $settings = array(
            'company_company_name'=>get_option('company_company_name'),
            'company_coord_key'=>get_option('company_coord_key'),
            'company_bing_key'=>get_option('company_bing_key'),
            'company_rate_per_mile'=>get_option('company_rate_per_mile'),
            'company_flat_delivery_price'=>get_option('company_flat_delivery_price'),
            'company_local_radius'=>get_option('company_local_radius'),
            'company_wait_price'=>get_option('company_wait_price'),
            'company_threshold_miles'=>get_option('company_threshold_miles'),
            'company_live_unload_save_price'=>get_option('company_live_unload_save_price'),
            'company_16_container_price'=>get_option('company_16_container_price'),
            'company_20_container_price'=>get_option('company_20_container_price'),
            'company_long_distance_threshold'=>get_option('company_long_distance_threshold'),
            'company_long_distance_mile_price'=>get_option('company_long_distance_mile_price'),
            'company_email_lists'=>implode(',',get_option('company_email_lists')),
            'company_warehouse_names'=>implode(',',get_option('company_warehouse_names')),
            'company_warehouse_addresses'=>implode('/',get_option('company_warehouse_addresses')),
            'company_warehouse_indoor_prices'=>implode(',',get_option('company_warehouse_indoor_prices')),
            'company_warehouse_outdoor_prices'=>implode(',',get_option('company_warehouse_outdoor_prices')),
            'company_damage_waiver'=>get_option('company_damage_waiver'),
            'company_contnets_protection'=>get_option('company_contnets_protection'),
            'company_toll_names'=>get_option('company_toll_names'),
            'company_toll_amounts'=>get_option('company_toll_amounts'),
            'company_custom_message'=>get_option('company_custom_message'),
            'company_signature'=>get_option('company_signature'),
            'company_main_button_color'=>get_option('company_main_button_color'),
        );

        return $settings;
    }
    public function request_get( ) {

        return array('test'=>'hello');

    }
    public function request_quote( WP_REST_Request $request) {
        $to_email = $request->get_param('email');
        $content = $request->get_param('content');
        $forward =(int) $request->get_param('forward');

        $title = "Here's your quote from ". get_option('company_company_name')." Storage";

        if ($forward==1)
            $title = 'I am ready to reserve, We will call you shortly';

        $result = wp_mail(
          $to_email,
          __($title, 'mailgun'),
          $content,
          array('Content-type: text/html')
        );


        return array('result'=>$result, 'forward'=>$forward);
    }

    public function my_customize_rest_cors(){
        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
        add_filter( 'rest_pre_serve_request', function( $value ) {
            header( 'Access-Control-Allow-Origin: *' );
            header( 'Access-Control-Allow-Methods: GET,POST' );
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Expose-Headers: Link', false );
            return $value;
        } );
    }

}