<?php
/**
 * Plugin Name: WooCommerce getSwift Shipping
 * Plugin URI: https://example.com
 * Description: Obtain shipping rates dynamically via the getSwift API for your orders.
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://example
 *
 * Copyright: 2009-2011 Automattic.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );


/**
 * Plugin activation check
 */
function wc_getSwift_activation_check(){
	if ( ! class_exists( 'SoapClient' ) ) {
		wp_die( 'Sorry, but you cannot run this plugin, it requires the <a href="http://php.net/manual/en/class.soapclient.php">SOAP</a> support on your server/hosting to function.' );
	}
}

register_activation_hook( __FILE__, 'wc_getSwift_activation_check');

class WC_Shipping_getSwift_Init {
	/**
	 * Plugin's version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/** @var object Class Instance */
	private static $instance;

	/**
	 * Get the class instance
	 */
	public static function get_instance() {
		return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
	}

	/**
	 * Initialize the plugin's public actions
	 */
	public function __construct() {
		if ( class_exists( 'WC_Shipping_Method' ) ) {
			add_action( 'admin_init', array( $this, 'maybe_install' ), 5 );
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'includes' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
			add_action( 'admin_notices', array( $this, 'environment_check' ) );
			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
            add_action( 'woocommerce_thankyou', array($this, 'getswift_woocommerce_thankyou'), 99 );

			$getwsift_settings = get_option( 'woocommerce_getswift_settings', array() );

		} else {
			add_action( 'admin_notices', array( $this, 'wc_deactivated' ) );
		}
	}

	/**
	 * environment_check function.
	 */
	public function environment_check() {
		if ( version_compare( WC_VERSION, '2.6.0', '<' ) ) {
			return;
		}

		if ( ! in_array( get_woocommerce_currency(), array( 'USD', 'CAD' ) ) || ! in_array( WC()->countries->get_base_country(), array( 'US', 'CA' ) ) ) {
			echo '<div class="error">
				<p>' . __( 'GetSwift requires that the WooCommerce currency is set to US Dollars and that the base country/region is set to United States.', 'woocommerce-shipping-getswift' ) . '</p>
			</div>';
		}
	}

	/**
	 * woocommerce_init_shipping_table_rate function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function includes() {
		if ( version_compare( WC_VERSION, '2.6.0', '<' ) ) {
			include_once(dirname(__FILE__) . '/includes/class-wc-shipping-getswift-deprecated.php');
		} else {
			include_once(dirname(__FILE__) . '/includes/class-wc-shipping-getswift.php');
		}
	}

	/**
	 * Add getSwift shipping method to WC
	 *
	 * @access public
	 * @param mixed $methods
	 * @return void
	 */
	public function add_method( $methods ) {
		if ( version_compare( WC_VERSION, '2.6.0', '<' ) ) {
			$methods[] = 'WC_Shipping_getSwift';
		} else {
			$methods['getswift'] = 'WC_Shipping_getSwift';
		}

		return $methods;
	}

	/**
	 * Localisation
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-shipping-getswift', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * WooCommerce not installed notice
	 */
	public function wc_deactivated() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce GetSwift Shipping requires %s to be installed and active.', 'woocommerce-shipping-getswift' ), '<a href="https://woocommerce.com" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}

	/**
	 * See if we need to install any upgrades
	 * and call the install
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function maybe_install() {
		// only need to do this for versions less than 1.0.0 to migrate
		// settings to shipping zone instance
		if ( ! defined( 'DOING_AJAX' )
		     && ! defined( 'IFRAME_REQUEST' )
		     && version_compare( WC_VERSION, '2.6.0', '>=' )
		     && version_compare( get_option( 'wc_getswift_version' ), '1.0.0', '<' ) ) {

			$this->install();

		}

		return true;
	}

	/**
	 * Update/migration script
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @access public
	 * @return bool
	 */
	public function install() {
		// get all saved settings and cache it
		$getswift_settings = get_option( 'woocommerce_getswift_settings', false );

		// settings exists
		if ( $getswift_settings ) {
			global $wpdb;

			// unset un-needed settings
			unset( $getswift_settings['enabled'] );
			unset( $getswift_settings['availability'] );
			unset( $getswift_settings['countries'] );

			// add it to the "rest of the world" zone when no fedex.
			if ( ! $this->is_zone_has_getswift( 0 ) ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}woocommerce_shipping_zone_methods ( zone_id, method_id, method_order, is_enabled ) VALUES ( %d, %s, %d, %d )", 0, 'getswift', 1, 1 ) );
				// add settings to the newly created instance to options table
				$instance = $wpdb->insert_id;
				add_option( 'woocommerce_getswift_' . $instance . '_settings', $getswift_settings );
			}

			update_option( 'woocommerce_getswift_show_upgrade_notice', 'yes' );
		}

		update_option( 'wc_getswift_version', $this->version );
	}

	/**
	 * Show the user a notice for plugin updates
	 *
	 * @since 1.0.0
	 */
	public function upgrade_notice() {
		$show_notice = get_option( 'woocommerce_getswift_show_upgrade_notice' );

		if ( 'yes' !== $show_notice ) {
			return;
		}

		$query_args = array( 'page' => 'wc-settings', 'tab' => 'shipping' );
		$zones_admin_url = add_query_arg( $query_args, get_admin_url() . 'admin.php' );
		?>
		<div class="notice notice-success is-dismissible wc-fedex-notice">
			<p><?php echo sprintf( __( 'getSwift now supports shipping zones. The zone settings were added to a new getSwift method on the "Rest of the World" Zone. See the zones %shere%s ', 'woocommerce-shipping-getswift' ),'<a href="' .$zones_admin_url. '">','</a>' ); ?></p>
		</div>

		<?php
	}

	/**
	 * Turn of the dismisable upgrade notice.
	 * @since 1.0.0
	 */
	public function dismiss_upgrade_notice() {
		update_option( 'woocommerce_getswift_show_upgrade_notice', 'no' );
	}

	/**
	 * Helper method to check whether given zone_id has getswift method instance.
	 *
	 * @since 1.0.0
	 *
	 * @param int $zone_id Zone ID
	 *
	 * @return bool True if given zone_id has fedex method instance
	 */
	public function is_zone_has_getswift( $zone_id ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(instance_id) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'getswift' AND zone_id = %d", $zone_id ) ) > 0;
	}

	public function getswift_woocommerce_thankyou($order_id){

        $order = new WC_Order( $order_id );

        $order_items = $order->get_items();

        $items = array();
        foreach ($order_items as $item){
            $product = $order->get_product_from_item( $item );
            $sku = $product->get_sku();

            $items[] = array(
              'quantity' => $item['qty'],
              'sku' => $sku,
              'description' => $item['name'],
              'price' => $item['line_subtotal'],

            );

        }

	    $settings = (array)get_option('woocommerce_getswift_settings');

	    $api_key = $settings['api_key'];
	    $name = $settings['name'];
	    $phone = $settings['phone'];
	    $email = $settings['email'];
	    $address = $settings['address'];

        $url = "https://app.getswift.co/api/v2/deliveries";

        $ch = curl_init($url);

        $request = array(
            'apiKey'    => $api_key,
            'booking'   => array(
              'pickupDetail'=>array(
                'name'      =>  $name,
                'phone'     =>  $phone,
                'email'     =>  $email,
                'address'   =>  $address
                ),
              'dropoffDetail' =>array(
                "name"      => $order->billing_first_name.' '.$order->billing_last_name,
                "phone"     => $order->billing_phone,
                "email"     => $order->billing_email,
                "address"   => $order->billing_address_1.' '. $order->billing_address_2, $order->billing_city.','.$order->billing_state.','.$order->billing_postcode
                ),
                'items' => $items
             ),
        );
        $request_json = json_encode($request);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_json );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        //Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        update_option($order_id.'_swift', $result->delivery->id);
        echo '<pre>';
        print_r($result);
        echo '</pre>';
    }

}

add_action( 'plugins_loaded' , array('WC_Shipping_getSwift_Init', 'get_instance' ), 0 );
