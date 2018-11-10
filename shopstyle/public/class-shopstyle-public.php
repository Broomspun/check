<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 * @author     Your Name <email@example.com>
 */
class Shopstyle_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
//		wp_enqueue_style( 'bootstrap-origin','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', array() , '3.3.7','all' );
//		wp_enqueue_style( 'bootstrap-css',plugin_dir_url( __FILE__ ) .'css/bootstrap.min.css');
		wp_enqueue_style( 'animate-css',plugin_dir_url( __FILE__ ) .'css/animate.min.css');
		wp_enqueue_style( 'noslider-css',plugin_dir_url( __FILE__ ) .'css/nouislider.min.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shopstyle-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( 'wNumb-js',plugin_dir_url( __FILE__ ) .'js/wNumb.js');
		wp_enqueue_script( 'noui-slider-js',plugin_dir_url( __FILE__ ) .'js/nouislider.min.js');
		wp_enqueue_script( 'bootstrap-js','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'angular-js','https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'app-js', plugin_dir_url( __FILE__ ) . 'js/app.js');
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shopstyle-public.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_parms', array('ajaxurl' => admin_url('admin-ajax.php')));
	}

    public function display_products($attrs){
        extract(shortcode_atts(array(
          'cat'=>'',
          'discount'=>'',
          'color'=>'',
          'min_price'=>'',
          'max_price'=>'',
          'brand'=>'',
          'retailer'=>'',
        ), $attrs));
        //get_loader
        $loader = Shopstyle::$instance;
        $plugin_admin = new Shopstyle_Admin( $loader->get_plugin_name(), $loader->get_version() );
        $apikey =$plugin_admin->getAPIToken();

        $wgt = new Shopstyle_widget();
        $settings = $wgt->get_settings();
//        $cats = $settings['choose-category'];

        $page_id = get_the_ID();
        if($page_id==693)
            $level2_cats = $settings[2]['choose-category'];
        elseif($page_id==701)
            $level2_cats = $settings[3]['choose-category'];
        elseif($page_id==706)
            $level2_cats = $settings[5]['choose-category'];

//        echo '<pre>';
//
//        print_r($settings);
//        echo '</pre>';
//        print_r($cats);


        ob_start();
        if($cat=='') {
            if(count($level2_cats)==1)
                $cat = $level2_cats[0];
            elseif(count($level2_cats)>1){
                $counts = count($level2_cats);
                $random = rand(0,$counts);
                $cat = $level2_cats[$random];
            }
        }
        $search_key = '';

        $results = $this->getProducts($apikey,$search_key, $cat);
        ?>

            <div class="row shopstyle-frontend">
                <div id="page-num" data-total="<?php echo $results['total'];?>" data-min-price="" data-max-price="" data-discount="0" data-page="0" data-cat="<?php echo $cat;?>" data-api="<?php echo $apikey;?>" data-keyword="<?php echo $search_key;?>"></div>
            <?php echo $results['html']; ?>
            </div>
            <div id="spinner" style="width: 100%; margin: 0 auto; text-align: center;">
                <img src="<?php echo plugin_dir_url( __FILE__ ) ?>img/spinner.gif" width="200" height="200" />
            </div>
    <?php
            $output = ob_get_contents(); // end output buffering
        ob_end_clean(); // grab the buffer contents and empty the buffer
        return $output;
    }
    /**
     * Gets products from shopstyle
     *
     * @since    1.0.0
     * @param      string    $api           Shopstyle API key.
     * @param      string    $search_key    Search keyword
     * @param      string    $category      prodyct category
     * @param      int       $offest        starting index
     * @param      int       $offest        number of products to fetch
     */
    private function getProducts($api, $search_key="", $category="handbags",$offset=0, $discount=0, $min_price="", $max_price="", $color='',$size='',$brand="", $retailer=''){

        $limit = 50;
        $parameters = array(
            "pid" => $api,
            "fts" => $search_key,
            'offset' => $offset * $limit,
            'limit' => 50,
        );

        $categories = explode(',', $category);

        if (count($categories)>1) {
                $random = rand(0, count($categories));
                $level2_cats = $categories;
                $parameters['cat'] = $level2_cats[$random];
        }
        elseif(count($categories)==1)
            $parameters['cat'] = $categories[0];
        else
            $parameters['cat'] = '';

        if($discount==1){
//            $parameters['d']='on-sale';
            $parameters['fl']='d';
        }
        if($min_price!='')
            $parameters['min-price']=(int)substr($min_price,1);

        if($max_price!='')
            $parameters['max-price']=(int)substr($max_price,1);

        if($color!='')
            $parameters['c']=$color;

        $size_v = "";
        if($size!='') {
            $sizes = explode(',', $size);

            foreach ($sizes as $v)
                $size_v .= "&fl=s".$v;
        }

        $brands_url = "";
        if($brand!='') {
            $brands = explode(',', $brand);

            foreach ($brands as $v)
                $brands_url .= "&fl=b".$v;
        }

        $retailers_url = "";
        if($retailer!='') {
            $retailers = explode(',', $retailer);

            foreach ($retailers as $v)
                $retailers_url .= "&fl=r".$v;
        }

        $getdata = http_build_query($parameters);

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/products?" . $getdata;

        if($size_v!='') $url .= $size_v;
        if($brands_url!='') $url .= $brands_url;
        if($retailers_url!='') $url .= $retailers_url;

        $apiURL = $url;

        curl_setopt($ch, CURLOPT_URL, $url);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_DNS_USE_GLOBAL_CACHE => 0
        );
        curl_setopt_array($ch, $options);
        $returnResult = curl_exec($ch);
        curl_close($ch);

        $all_data = json_decode($returnResult, true);

        $metadata = $all_data['metadata'];
        $totals = $metadata['total'];
        $products = $all_data['products'];

        if($totals==0){
            $data['html'] = "<h2 class='bs-component' style='text-align: center;'>We could not find any products. Try another search to find more products</h2>";
            $data['total'] = 0;
            $data['url'] = $apiURL;

            return $data;
        }

        $html = '';

        foreach ($products as $product){
            $url = $product['clickUrl'];
            $description = substr($product['description'], 0, 20).'...';
            $price = $product['price'];

            if(array_key_exists('saleprice', $product))
                $price = $product['saleprice'];

            $discount = (int)$product['discount'];

            $card = '';
            $card .= '<div class="span3 bs-component zoomIn" data-price="'.$price.'" style="display: flex;"><div style="flex: 1;" class="card" >';
            $card .= '<a target="blank" href="'.$url.'" >';
            if($discount>0)
                $card .='<div class="discount-symbol"><span>-'.$discount.'%</span></div>';

            $card .= '<div class="card-header text-muted text-center"><h5><strong>'.$product['name'].'</strong></h5></div>';
            $card .= '<div class="card-body" style="text-align: center;">';

            $card .= '<div class="img-container"><img class="img-responsive" src="'.$product['image']['sizes']['Medium']['url'].'">';
            $card .= '</div></div>';
            $card .= '<div class="card-footer text-muted text-center"><span>'.$description.'</span>';
            if($discount || array_key_exists('salePriceLabel', $product))
                $card .='<h5><del style="color: #888;">'.$product['priceLabel'].'</del>&nbsp;&nbsp;<span><strong>'.$product['salePriceLabel'].'</strong></span></h5></div>';
            else
                $card .= '<h5><strong>'.$product['priceLabel'].'</strong></h5></div>';

            $card .='</a></div></div>';

            $html .= $card;
        }

        $data = array("total"=>$totals, 'html'=> $html, 'url'=>$apiURL);
       return $data;
    }

    public function get_products_frontend(){
        $api = $_POST['api_token'];
        $search_key = $_POST['keyword'];
        $category = $_POST['cat'];
        $offset = $_POST['pg_number'];
        $discount = $_POST['discount'];
        $min_price = $_POST['min_price'];
        $max_price = $_POST['max_price'];
        $color = $_POST['color'];
        $size = $_POST['size'];
        $brand = $_POST['brand'];
        $retailer = $_POST['retailer'];

        $result = $this->getProducts($api, $search_key, $category, $offset,$discount, $min_price, $max_price, $color, $size, $brand, $retailer);

        $data['in'] = $_POST;
        $data['html'] = $result['html'];
        $data['total'] = $result['total'];
        $data['url'] = $result['url'];

        echo json_encode($data);
        wp_die();
    }

}
