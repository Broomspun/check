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
class Shopstyle_Admin {

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
     * key of Shopstyle Product Search API.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $APIToken;
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

        $this->APIToken = "uid224-39609668-69";
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
        wp_enqueue_style( 'choson-css', plugin_dir_url( __FILE__ ) . 'css/chosen.min.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shopstyle-admin.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( 'choson-js', plugin_dir_url( __FILE__ ) . 'js/chosen.jquery.min.js', array( 'jquery' ), '1.8.2', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shopstyle-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_parms', array('ajaxurl' => admin_url('admin-ajax.php')));

	}
	public function shopstyle_admin_menu(){
        add_menu_page('Shopstyle API Settings', 'Shopstyle API Setup', 'manage_options', 'shopstyle_api_setting', array($this, 'shopstyle_setting'));
    }

    public function shopstyle_setting(){
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require_once ('partials/shopstyle-admin-display.php');
    }

    public function upload_products()
    {
        set_time_limit(0);
        session_start();

        $products = $_SESSION['product'];
        $items = $_POST['items'];

        $data = array();

        foreach ($items as $item) {
            $this->write_product($products[$item]);
            $data['input'] = $products[$item];
//                $this->writeCSV($products[$item], $file);
        }

        $data['message'] = 'Uploaded succesufully!';
        echo json_encode($data);
        wp_die();
    }

    private function write_product($product)
    {
        $user_id = wp_get_current_user()->ID;
        $title = $product['title'];
        $post = array(
            'post_author' => $user_id,
            'post_content' => '',
            'post_status' => "publish",
            'post_title' => $title,
            'post_parent' => '',
            'post_type' => "product",
            'post_excerpt' => $product['shortdescription'],
        );

        // Insert the product into the database
        $post_id = wp_insert_post($post, true);

        update_post_meta($post_id, '_sku', $product['sku']);
        update_post_meta($post_id, '_visibility', 'visible');
        update_post_meta($post_id, 'shopstyle_external_url', $product['link']);


        update_post_meta($post_id, 'retailer', $product['retailer']);

        wp_set_object_terms($post_id, 'variable', 'product_type');

        if(isset($product['size']) && isset($product['color']))
            $available_attributes = array('size', 'color');
        elseif(isset($product['size']) && !isset($product['color']))
            $available_attributes = array('size');
        elseif(!isset($product['size']) && isset($product['color']))
            $available_attributes = array('color');

        $this->insert_product_attributes($post_id, $available_attributes, $product['stock']); // Add attributes passing the new post id, attributes & variations
        $this->insert_product_variations($post_id, $product['stock']); // Insert variations passing the new post id & variations

/////////////////////////////////////////////////////////////////////////
///
///                 Create categories
///
////////////////////////////////////////////////////////////////////////////

        $cat_names = explode('|', $product['categories_names']);
        $parent_term_id = wp_set_object_terms($post_id, $cat_names[0], 'product_cat');

        $slug = str_replace(' ', '-', str_replace("'", '', strtolower($cat_names[1])));
        if (isset($cat_names[1])) {
            if (!term_exists($cat_names[1], 'product_cat', $parent_term_id[0])) {
                $parent_term = term_exists($cat_names[0], 'product_cat'); // array is returned if taxonomy is given
                $parent_term_id = $parent_term['term_id'];

                wp_insert_term($cat_names[1], 'product_cat', array('parent' => $parent_term_id, 'slug' => $slug));
            }
            wp_set_object_terms($post_id, $cat_names[1], 'product_cat', true);

        }

        if (isset($cat_names[2])) {
            if (!term_exists($cat_names[2], 'product_cat')) {
                $parent_term = term_exists($cat_names[1], 'product_cat'); // array is returned if taxonomy is given
                $parent_term_id = $parent_term['term_id'];

                wp_insert_term($cat_names[2], 'product_cat', array('parent' => $parent_term_id));
            }
            wp_set_object_terms($post_id, $cat_names[2], 'product_cat', true);

        }


//===============  Upload Featured image ================
        //
        if (isset($product['feature_image'])) {
            $image_url = $product['feature_image'];
            $this->uploadImage($image_url, $user_id, $post_id, true);
        }

//===============  Upload Gallery images ================
        if (isset($product['gallery'])) {
            $galleries = $product['gallery'];
            $img_ids = array();
            foreach ($galleries as $gallery) {
                $img_ids[] = $this->uploadImage($gallery, $user_id, $post_id);
            }
            update_post_meta($post_id, '_product_image_gallery', implode(',', $img_ids));
        }
    }

    public function import_products_ajax_request()
    {
        // get post
        if (!isset($_POST['shopstyle_import_nonce'])) {
            exit(1);
            return;
        }

        if (empty($_SESSION)) {
            session_start();
        } else {
            session_destroy();
            session_start();
        }

        $token = $_POST['shopstyle_api_token'];
        $limit = $_POST['shopstyle_ppp'];
        $search_key = $_POST['shopstyle_keywords'];

        $parameters = array(
            "pid" => $this->APIToken,
            "fts" => $search_key,
            'offset' => $_POST['pg_number'] * $limit,
            'limit' => $limit,
        );

        $category = $_POST['category'];
        if ($category != '-1') {
            $parameters['cat'] = $category;
        }

        $getdata = http_build_query($parameters);

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/products?" . $getdata;
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
        $products = $all_data['products'];

        $data = array();

        $totals = (int)$metadata['total'];
        if ($totals > 5000) $totals = 5000;
        $data['totals'] = $totals;
        $data['url'] = $url;

        $pages = ceil($totals / $_POST['shopstyle_ppp']);
        $data['pages'] = $pages;
        $data['products'] = $returnResult;

        $i = 0;

        $items = array();

        foreach ($products as $product) {
            foreach ($product as $key => $value) {
                if ($key == 'id') {
                    $id = (string)$value;
                    $data['product'][$id]['sku'] = (string)$value;
                    $items[] = $id;
                }
                if ($key == 'name') $data['product'][$id]['title'] = (string)$value;
                if ($key == 'brandedName') $data['product'][$id]['brandedName'] = (string)$value;
                if ($key == 'unbrandedName') $data['product'][$id]['unbrandedName'] = (string)$value;

                if ($key == 'clickUrl') $data['product'][$id]['link'] = (string)$value;
                if ($key == 'locale') $data['product'][$id]['locale'] = (string)$value;

                if ($key == 'currency') $data['product'][$id]['currency'] = (string)$value;
                if ($key == 'price') $data['product'][$id]['price'] = (string)$value;
                if ($key == 'salePrice') $data['product'][$id]['saleprice'] = (string)$value;

                if ($key == 'description') $data['product'][$id]['shortdescription'] = (string)$value;
                if ($key == 'extractDate') $data['product'][$id]['extractDate'] = (string)$value;
                if ($key == 'seeMoreLabel') $data['product'][$id]['seeMoreLabel'] = (string)$value;

                if ($key == 'promotionalDeal') {
                    $deals = $value;

                    $data['product'][$id]['promoDeal_id']   = $deals['id'];
                    $data['product'][$id]['promoDeal_type'] = $deals['type'];
                    $data['product'][$id]['promoDeal_typeLabel'] = $deals['typeLabel'];
                    $data['product'][$id]['promoDeal_title'] = $deals['title'];
                    $data['product'][$id]['promoDeal_shortTitle'] = $deals['shortTitle'];
                    $data['product'][$id]['promoDeal_startDate'] = $deals['startDate']['date'];
                    $data['product'][$id]['promoDeal_startDate_friendly'] = $deals['startDate']['friendly'];
                    $data['product'][$id]['promoDeal_startDate_timestamp'] = $deals['startDate']['timestamp'];
                    $data['product'][$id]['promoDeal_endDate'] = $deals['endDate']['date'];
                    $data['product'][$id]['promoDeal_endDate_friendly'] = $deals['endDate']['friendly'];
                    $data['product'][$id]['promoDeal_endDate_timestamp'] = $deals['endDate']['timestamp'];
                }


                if ($key == 'image') {
                    $image = $value;
                    $featured_image = $image['sizes']['Original']['url'];

                    $data['product'][$id]['feature_image'] = (string)$featured_image;
                    $data['product'][$id]['thumbnail'] = $image['sizes']['Medium']['url'];
                    if(isset($image['sizes']['Small']['url']))
                        $data['product'][$id]['images']['small'] = $image['sizes']['Small']['url'];
                    $data['product'][$id]['images']['xlarge'] = $image['sizes']['XLarge']['url'];
                    $data['product'][$id]['images']['medium'] = $image['sizes']['Medium']['url'];
                    $data['product'][$id]['images']['large'] = $image['sizes']['Large']['url'];
                    $data['product'][$id]['images']['iphonesmall'] = $image['sizes']['IPhoneSmall']['url'];
                    $data['product'][$id]['images']['best'] = $image['sizes']['Best']['url'];
                    $data['product'][$id]['images']['original'] = $image['sizes']['Original']['url'];
                    $data['product'][$id]['images']['iphone'] = $image['sizes']['IPhone']['url'];
                }

                if ($key == 'alternateImages') {
                    $galleries = $value;

                    foreach ($galleries as $gallery) {
                        $data['product'][$id]['gallery'][] = $gallery['sizes']['Original']['url'];
                        $data['product'][$id]['small_gallery'][] = $gallery['sizes']['Medium']['url'];
                    }
                }
                if ($key == 'colors') {
                    $colors = $value;
                    foreach ($colors as $color) {
                        $data['product'][$id]['color']['name'][] = $color['name'];
                        $data['product'][$id]['color']['image'][] = $color['image']['sizes']['Original']['url'];
                    }
                }

                if ($key == 'sizes') {
                    $sizes = $value;
                    foreach ($sizes as $size) {
                        $data['product'][$id]['size']['name'][] = $size['name'];
                        $data['product'][$id]['size']['canonicalSize'][] = $size['canonicalSize']['name'];
                    }
                }

                if ($key == 'stock') {
                    $stocks = $value;
                    foreach ($stocks as $stock) {
                        $sale_price = '';
                        if (isset($data['product'][$id]['saleprice']))
                            $sale_price = $data['product'][$id]['saleprice'];

                        if(isset($stock['color']) && isset($stock['size']))
                            $attribute = array('color' => $stock['color']['name'], 'size' => $stock['size']['name']);
                        elseif(isset($stock['color']) && !isset($stock['size']))
                            $attribute = array('color' => $stock['color']['name']);
                        elseif(!isset($stock['color']) && isset($stock['size']))
                            $attribute = array('size' => $stock['size']['name']);

                        $data['product'][$id]['stock'][] = array(
                            'attributes' => $attribute,
                            'price' => $data['product'][$id]['price'],
                            'saleprice' => $sale_price,
                        );
                    }
                }

                if ($key == 'retailer') {
                    $data['product'][$id]['retailer']['id'] = $value['id'];
                    $data['product'][$id]['retailer']['name'] = $value['name'];
                    $data['product'][$id]['retailer']['score'] = $value['score'];
                }
                if ($key == 'brand') {
                    $data['product'][$id]['brand']['id'] = $value['id'];
                    $data['product'][$id]['brand']['name'] = $value['name'];
                }

                if ($key == 'categories') {
                    $cats = $value;
                    foreach ($cats as $cat) {
                        $cat_id = $cat['id'];
                        $cat_fullname = $cat['fullName'];
                    }

                    $id0 = $cat_id;
                    $fullname = $cat_fullname;

                    $cat_ids = array();
                    $cat_names = array();

                    for ($ii = 0; $ii < 25; $ii++) {
                        $parent_cat = $this->getParentCategory($token, $id0);

                        if ($parent_cat['parentId'] == 'clothes-shoes-and-jewelry') {

                            $cat_ids[] = $parent_cat['id'];
                            $cat_names[] = $parent_cat['cat_name'];

                            break;
                        }

                        $cat_ids[] = $parent_cat['id'];
                        $cat_names[] = $parent_cat['cat_name'];

                        $id0 = $parent_cat['parentId'];
                    }

                    $cat_ids = array_reverse($cat_ids);
                    $cat_names = array_reverse($cat_names);

                    $data['product'][$id]['categories_ids'] = implode('|', $cat_ids);
                    $data['product'][$id]['categories_names'] = implode('|', $cat_names);
                }
            }

            $i++;
        }

        $csv_filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'products.csv';
        $file = fopen($csv_filename, 'w');
        $header = $this->getHeader();
        // save the column headers
        fputcsv($file, $header);

        foreach ($items as $item) {
            $this->writeCSV($data['product'][$item], $file);
        }

        $_SESSION = $data;

        echo json_encode($data);
        wp_die();
    }

    private function uploadImage($image_url, $user_id, $post_id, $bFeatured = false)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);

        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . $filename;
        else
            $file = $upload_dir['basedir'] . '/' . $filename;

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);


        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit',
            'post_author' => $user_id,
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
//            if(!$bFeatured)
//                return $attach_id;

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
        return $attach_id;
    }

    public function getCategoriesOfShopstyle()
    {
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
                'depth' => 3,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/categories?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        /////////////////////////// Get all Brands   /////////////////////////

        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/brands?" . $getdata;
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
        $resultBrands = curl_exec($ch);
        curl_close($ch);


        $all_data = json_decode($result, true);

        $metadata = $all_data['metadata'];
        $categories = $all_data['categories'];

        $data['cat'] = $categories;
        $data['meta'] = $metadata;

        $root_cat_id = "clothes-shoes-and-jewelry";

        $html = '';
        $level2_cats = array('handbags','mens-grooming','womens-accessories','mens-accessories');
        foreach ($categories as $category) {
            $parent_id = $category['parentId'];
            if ($parent_id == $root_cat_id) {
                $level_1_id = $category['id'];
                continue;
                $html .= '<option class="level-1" value="' . $category['id'] . '">' . $category['fullName'] . '</option>';
            } else if ($parent_id == $level_1_id) {
                if(!in_array($category['id'], $level2_cats)) continue;
                $level_2_id = $category['id'];
                $html .= '<option class="level-2" value="' . $category['id'] . '">&nbsp;&nbsp;' . $category['fullName'] . '</option>';
            } else if ($parent_id == $level_2_id) {
                $html .= '<option class="level-3" value="' . $category['id'] . '">&nbsp;&nbsp;&nbsp;&nbsp;' . $category['fullName'] . '</option>';
            }
        }
        $data['html'] = $html;
        echo json_encode($data);
        wp_die();
    }

    private function writeCSV($product,$fp){
        $items = array(
            "id"                            =>  $product['sku'],
            "name"                          =>  $product['title'],
            "brandedName"                   =>  $product['brandedName'],
            "unbrandedName"                 =>  $product['unbrandedName'],
            "currency"                      =>  $product['currency'],
            "price"                         =>  $product['price'],
            "salePrice"                     =>  $product['saleprice'],
            "retailer_id"                   =>  $product['retailer']['id'],
            "retailer_name"                 =>  $product['retailer']['name'],
            "retailer_score"                =>  $product['retailer']['score'],
            "brand_id"                      =>  $product['brand']['id'],
            "brand_name"                    =>  $product['brand']['name'],
            "locale"                        =>  $product['locale'],
            "description"                   =>  $product['shortdescription'],
            "clickUrl"                      =>  $product['link'],
            "image_Small"                   =>  $product['images']['small'],
            "image_XLarge"                  =>  $product['images']['xlarge'],
            "image_Medium"                  =>  $product['images']['medium'],
            "image_Large"                   =>  $product['images']['large'],
            "image_IPhoneSmall"             =>  $product['images']['iphonesmall'],
            "image_Best"                    =>  $product['images']['best'],
            "image_Original"                =>  $product['images']['original'],
            "image_IPhone"                  =>  $product['images']['iphone'],
            "galleries"                     =>  implode(',',$product['gallery']),
            "colors"                        =>  implode(',',$product['color']['name']),
            "color_images"                  =>  implode(',',$product['color']['image']),
            "sizes"                         =>  implode(',',$product['size']['name']),
            "categories"                    =>  $product['categories_names'],
            "extractDate"                   =>  $product['extractDate'],
            "seeMoreLabel"                  =>  $product['seeMoreLabel'],
            "promoDeal_id"                  =>  $product['promoDeal_id'],
            "promoDeal_type"                =>  $product['promoDeal_type'],
            "promoDeal_typeLabel"           =>  $product['promoDeal_typeLabel'],
            "promoDeal_title"               =>  $product['promoDeal_title'],
            "promoDeal_shortTitle"          =>  $product['promoDeal_shortTitle'],
            "promoDeal_startDate"           =>  $product['promoDeal_startDate'],
            "promoDeal_startDate_friendly"  =>  $product['promoDeal_startDate_friendly'],
            "promoDeal_startDate_timestamp" =>  $product['promoDeal_startDate_timestamp'],
            "promoDeal_endDate"             =>  $product['promoDeal_endDate'],
            "promoDeal_endDate_friendly"    =>  $product['promoDeal_endDate_friendly'],
            "promoDeal_endDate_timestamp"    =>  $product['promoDeal_endDate_timestamp']
        );
        fputcsv($fp, $items);
    }

    private function getParentCategory($token, $id)
    {
        $getdata = http_build_query(array(
            'pid' => $token,
            'cat' => $id
        ));

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/categories?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        $all_data = json_decode($result, true);
        $id = $all_data['metadata']['root']['id'];
        $parentId = $all_data['metadata']['root']['parentId'];
        $fullname = $all_data['metadata']['root']['fullName'];

        return array(
            'id' => $id,
            'parentId' => $parentId,
            'cat_name' => $fullname
        );
    }

    private function getHeader(){
        $header = array("id","name","brandedName","unbrandedName","currency","price","salePrice",
            "retailer_id","retailer_name","retailer_score", "brand_id","brand_name","locale","description","clickUrl",
            "image_Small","image_XLarge", "image_Medium","image_Large","image_IPhoneSmall","image_Best","image_Original", "image_IPhone",
            "galleries", "colors","color_images","sizes","categories","extractDate","seeMoreLabel",
            "promoDeal_id","promoDeal_type","promoDeal_typeLabel","promoDeal_title","promoDeal_shortTitle",
            "promoDeal_startDate","promoDeal_startDate_friendly","promoDeal_startDate_timestamp",
            "promoDeal_endDate","promoDeal_endDate_friendly","promoDeal_endDate_timestamp"
        );
        return $header;
    }

    public function getAPIToken(){
        return $this->APIToken;
    }

    public function getCategories($chosenCats){
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
                'depth' => 3,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/categories?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        $all_data = json_decode($result, true);

        $metadata = $all_data['metadata'];
        $categories = $all_data['categories'];

        $data['cat'] = $categories;
        $data['meta'] = $metadata;

        $all_cats = array();

        $id = 0;
        if(!empty($chosenCats)) {
            $cat_values = implode(',', $chosenCats);
            foreach ($categories as $category) {
                $parent_id = $category['parentId'];
                $cat_id = $category['id'];
                $cat_name = $category['fullName'];

                if (in_array($cat_id, $chosenCats)) {
                    $id++;
                    $all_cats[$id] = array('id' => $cat_id, 'name' => $cat_name);
                } elseif (in_array($parent_id, $chosenCats))
                    $all_cats[$id]['child'][$cat_id] = $cat_name;
            }
        } else {
            $cat_values = '';
            $id= 0;
            $root_cat_id = "clothes-shoes-and-jewelry";
            foreach ($categories as $category) {
                $cat_id = $category['id'];
                $parent_id = $category['parentId'];
                $cat_name = $category['fullName'];

                 if ($parent_id == $root_cat_id) {
                     $level_1_id = $category['id'];
                      continue;
                    } else if ($parent_id == $level_1_id) {
                        $id++;
                        $level_2_id = $category['id'];
                        $all_cats[$id] = array('id' => $cat_id, 'name' => $cat_name);
                    } else if ($parent_id == $level_2_id) {
                     $all_cats[$id]['child'][$cat_id] = $cat_name;
                    }
            }
        }

        $html = '<div class="shopstyle-cats-wrap"><ul class="shopstyle-cats"><li class="level-2 active" data-cat="'.$cat_values.'" data-catid="">All</li>';
        foreach ($all_cats as $cat){
            $id = $cat['id'];
            $name = $cat['name'];
            $child = $cat['child'];
            $html .= '<li class="level-2" data-catid="'.$id.'">'.$name;
            $html .= '<ul class="level-3">';

            foreach ($child as $k=>$v){
                $html .= '<li class="level-3" data-catid="'.$k.'">'.$v.'</li>';
            }
            $html .= '</ul></li>';
        }
        $html .=  '</ul></div>';
        return $html;
    }

    public function getColors(){
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/colors?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result,true);

        $colors = $result['colors'];

        $color_tables = array('1'=>'#9f3c17','3'=>'#f26f2b','4'=>'#ffd736', '7'=>'#ff5034',
            '8'=>'#c770e0','10'=>'#269bee','13'=>'#14e000','14'=>'#aaa','15'=>'#fbfbfb',
            '16'=>'#444', '17'=>'#ffabf8','18'=>'#ffd736','19'=>'##fbfbfb','20'=>'#f5f5dc');

        //18-Gold- integrate with yellow color[4]
        //19-silver- integrate with white color[15]


        $html = "<ul class='filter-color'>";

        for($i=0; $i<count($colors); $i++){

            $class='';
            if($colors[$i]['id']==15)
                $class = 'white';

            $html .= '<li class="'.$class.'" style="background-color: '.$color_tables[$colors[$i]['id']].'" title="'.$colors[$i]['name'].'" data-color="'.strtolower($colors[$i]['name']).'" >'.$colors[$i]['name'].'</li>';
        }

        $html .='</ul>';

        return $html;
    }

    public function getAllRetailers(){
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/retailers?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result,true);

        $retailers = $result['retailers'];
        usort($retailers, "scorecmp");

        $html =  '<div class="filter-retailer-wrap">';
        $html .='<i class="fa fa-search" aria-hidden="true"></i><input type="text" id="search-retailer" name="search-retailer" placeholder="Search Retailers...">';
        $html .='<hr>';
        $html .='<p>Top Retailers(All '.count($retailers).')</p>';
        $html .=  '<ul class="level-3 filter-retailer">';

        for($i=0; $i<count($retailers); $i++){
            if($i>20)
                $html .= '<li style="display:none;" class="level-3" data-retailer="'.$retailers[$i]['id'].'">'.$retailers[$i]['name'].'</li>';
            else
                $html .= '<li class="level-3" data-retailer="'.$retailers[$i]['id'].'">'.$retailers[$i]['name'].'</li>';
         }
         $html .= '</ul></div>';
        return $html;

    }
    public function getAllBrands(){
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/brands?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result,true);

        $brands = $result['brands'];
        usort($brands, "scorecmp");

        $html =  '<div class="filter-brand-wrap">';
        $html .='<i class="fa fa-search" aria-hidden="true"></i><input type="text" id="search-brand" name="search-brand" placeholder="Search Brands...">';
        $html .='<hr>';
        $html .='<p>Top Brands(All '.count($brands).')</p>';
        $html .=  '<ul class="level-3 filter-brand">';
        for($i=0; $i<count($brands); $i++){
            if($i>20)
                $html .= '<li style="display: none;" class="level-3" data-retailer="'.$brands[$i]['id'].'">'.$brands[$i]['name'].'</li>';
            else
                $html .= '<li class="level-3" data-retailer="'.$brands[$i]['id'].'">'.$brands[$i]['name'].'</li>';
        }
        $html .= '</ul></div>';
        return $html;

    }

    public function getCategoriesToChosen(){
        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
                'depth' => 2,
            )
        );

        $ch = curl_init();
        $url = "http://api.shopstyle.com/api/v2/categories?" . $getdata;
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
        $result = curl_exec($ch);
        curl_close($ch);

        /////////////////////////// Get all Brands   /////////////////////////

        $getdata = http_build_query(
            array(
                'pid' => $this->APIToken,
            )
        );

        $all_data = json_decode($result, true);

        $metadata = $all_data['metadata'];
        $categories = $all_data['categories'];

        $data['cat'] = $categories;
        $data['meta'] = $metadata;

        $root_cat_id = "clothes-shoes-and-jewelry";

        $all_cats = array();
        $cats = array();

        foreach ($categories as $category) {
            $parent_id = $category['parentId'];

            if ($parent_id == $root_cat_id) {
                $level_1_id = $category['id'];
                continue;
            }
            else if ($parent_id == $level_1_id) {

                if(!empty($cats))
                    $all_cats[] = $cats;

                $cats = array('id'=>$category['id'],'name'=>$category['fullName']);
            }
        }
        $html = '<div class="shopstyle-cats-chosen-wrap"><select data-placeholder="Choose Categories..." name="shopstyle-cats-choson" multiple class="shopstyle-cats-chosen" style="width: 100%;">';
        foreach ($all_cats as $cat){
            $id = $cat['id'];
            $name = $cat['name'];
            $child = $cat['child'];
            $html .= '<option value="'.$id.'" data-catid="'.$id.'">'.$name.'</option>';
        }
        $html .=  '</select></div>';
        return $all_cats;
    }
}
function scorecmp($a, $b)
{
    if ($a['score'] == $b['score']) {
        return 0;
    }
    return ($a['score'] > $b['score']) ? -1 : 1;
}
