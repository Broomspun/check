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
class myChart_Admin {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mychart-admin.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mychart-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_parms', array('ajaxurl' => admin_url('admin-ajax.php')));

	}

    /**
     * Create Admin menu for the admin area.
     *
     * @since    1.0.0
     */

    public function device_center_admin_menu(){
        add_menu_page('Device Center', 'Device Center', 'manage_options', 'device_center', array($this, 'device_center_setting'));
    }

    /**
     * Display Seeting  for the admin area.
     *
     * @since    1.0.0
     */
    public function device_center_setting(){
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require_once ('partials/mychart-admin-display.php');
    }

    public function save(){
        //Check nonce
        if (!isset($_POST['device_center_nonce'])) {
            exit(1);
            return;
        }

        $host =  trim($_POST['host']);
        $user =  trim($_POST['user']);
        $pass =  trim($_POST['pass']);
        $db =  trim($_POST['dbname']);
        $charset = 'utf8';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $conn = new PDO($dsn, $user, $pass, $opt);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $data['status'] = '200';
            $data['message'] = 'Connected successfully';

            $device_setting = array(
                'host'=>$host,
                'user'=>$user,
                'dbname'=>$db,
                'pass'=>$pass
            );

            update_option('device_center', $device_setting);
        }
        catch(PDOException $e)
        {
            $data['status'] = "404";
            $data['message'] = "Connection failed: " . $e->getMessage();

            update_option('device_center', '');
        }

        echo json_encode($data);
        wp_die();
    }

}
