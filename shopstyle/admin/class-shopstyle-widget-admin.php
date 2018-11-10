<?php
class Shopstyle_widget extends WP_Widget
{

    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        $widget_ops = array(
            'description' => 'Shopstyle widget',
        );
        parent::__construct(false, 'Shopstyle Widget', $widget_ops);
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        extract($args);

//        $title = apply_filters('widget_title', empty($instance['hot_title']) ? '' : $instance['hot_title']);
        $loader = Shopstyle::$instance;
        $plugin_admin = new Shopstyle_Admin( $loader->get_plugin_name(), $loader->get_version() );

        $cat = $instance['show-category'];
        $color = $instance['show-color'];
        $price = $instance['show-price'];
        $discount = $instance['show-discount'];
        $brand = $instance['show-brand'];
        $retailer = $instance['show-retailer'];
        $size = $instance['show-size'];
        $chosen_cats = $instance['choose-category'];

        echo '<div id="shopstyle-filter-wrap" ng-app="ShopstyleApp">';?>
        <div class="filter-search-keyword-wrap" style="position: relative">
        <i class="fa fa-search" aria-hidden="true"></i>
        <input id="search-product" type="text" placeholder="Search Products...">
        </div>
        <hr>
        <?php

        if($cat){
            echo '<h3>Categories</h3>';
            $categories = $plugin_admin->getCategories($chosen_cats);
            echo $categories;
        }
        if($discount){
            echo '<h3>Discounts</h3>';
            echo '<ul class="level-2" id="filter-discount">';
            echo '<li class="level-3 active" data-discount="0">All</li>';
            echo '<li class="level-3" data-discount="1">Only Discounted</li>';
            echo '</ul>';

        }
        if($price){
            echo '<h3>Prices</h3>';
            echo '<div class="price-wrapper"><div id="filter-price"></div>';
            echo '<div class="price-slider-values">
                    <span class="price-slider-value-min" id="price-slider-value-min">$0</span>
                    <span class="price-slider-value-max" id="price-slider-value-max">$1000</span>
                 </div></div>';
        }

        if($color){
            echo '<h3>Colors</h3>';
            echo $plugin_admin->getColors();
        }
        if($size){
            echo '<h3>Sizes</h3>';
            $sizes = array("XXS"=>79, "XS"=>81, "S"=>83, "M"=>83,"L"=>87,"XL"=>89);

            $lists = "";
            foreach ($sizes as $k=>$v){
                $lists .= '<li data-size="'.$v.'">'.$k.'</li>';
            }

            echo '<div class="filter-size-wrap"><ul class="filter-size">'.$lists.'</ul></div>';
        }
        if($brand){
            echo '<h3>Brands</h3>';
            ?>
            <div class="filter-brand-wrap">
                <div ng-controller="brandCtrl">
                    <i class="fa fa-search" aria-hidden="true"></i><input ng-model="searchbrand" id="search-brand" type="text" placeholder="Search Brands...">
                    <hr>
                    <p>Top Brands(<strong>Totals: {{items}}</strong>)</p>
                    <ul class="filter-brand">
                        <li class="level-3" ng-click="toggleCustomClass($event);" data-score="{{user.score}}" ng-repeat="user in users | filter : searchbrand | orderByScore:'score' | limitTo:200" data-brand="{{user.id}}">
                            {{user.name}}
                        </li>
                    </ul>
                </div>
            </div>
            <?php
//            echo $plugin_admin->getAllBrands();
        }
        if($retailer){
            echo '<h3>Retailers</h3>';
         ?>
            <div class="filter-retailer-wrap">
                <div ng-controller="retailerCtrl">
                    <i class="fa fa-search" aria-hidden="true"></i><input ng-model="searchretailer" id="search-retailer" type="text" placeholder="Search Retailert...">
                    <hr>
                    <p>Top Retailers(<strong>Totals: {{items}}</strong>)</p>
                    <ul class="filter-retailer">
                        <li class="level-3" ng-click="toggleCustomClass($event);" data-score="{{retailer.score}}" ng-repeat="retailer in retailers | filter : searchretailer | orderByScore:'score' | limitTo:200" data-retailer="{{retailer.id}}">
                            {{retailer.name}}
                        </li>
                    </ul>
                </div>
            </div>
         <?
//            echo $plugin_admin->getAllRetailers();
        }
        ?>

        <?php

        echo '</div>';
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {
        //Defaults
        $instance = wp_parse_args((array)$instance, array('choose-category'=>"",'show-category' => 1, 'show-color' => 1,
                    'show-discount'=>1,'show-price'=>1,'show-size'=>1,'show-brand'=>1,'show-retailer'=>1));

        $cateogy = esc_attr($instance['show-category']);
        $discount = esc_attr($instance['show-discount']);
        $price = esc_attr($instance['show-price']);
        $color = esc_attr($instance['show-color']);
        $size = esc_attr($instance['show-size']);
        $brand = esc_attr($instance['show-brand']);
        $retailer = esc_attr($instance['show-retailer']);
        $chosen_cats = $instance['choose-category'];


        if($cateogy) $check_cat = "checked";
        if($color) $check_color = "checked";
        if($discount) $discount = "checked";
        if($price) $price = "checked";
        if($size) $size = "checked";
        if($brand) $brand = "checked";
        if($retailer) $retailer = "checked";

        $loader = Shopstyle::$instance;
        $plugin_admin = new Shopstyle_Admin( $loader->get_plugin_name(), $loader->get_version() );

        echo '<h3>Please choose categories</h3>';

        $all_cats = $plugin_admin->getCategoriesToChosen();
        ?>
        <div class="shopstyle-cats-chosen-wrap">
            <select data-placeholder="Choose Categories..." id="<?php echo $this->get_field_id('choose-category') ?>" name="<?php echo $this->get_field_name('choose-category')?>[]" multiple="multiple" class="shopstyle-cats-chosen" style="width: 100%;">
                <?php
                foreach ($all_cats as $cat){
                    $id = $cat['id'];
                    $name = $cat['name'];
                    printf(
                        '<option value="%s" %s style="margin-bottom:3px;">%s</option>',
                        $id,
                        in_array( $id, $chosen_cats) ? 'selected="selected"' : '',
                        $name
                    );
//                    echo '<option value="'.$id.'" data-catid="'.$id.'">'.$name.'</option>';
                }
                ?>
            </select>
        </div>
        <?php

        echo '<p><label for="' . $this->get_field_id('show-category') . '">' . 'Enable Categories ' . '</label><input type="checkbox" style="float: left; margin-top: 5px;" '.$check_cat.' id="' . $this->get_field_id('show-category') . '" name="' . $this->get_field_name('show-category') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-discount') . '">' . 'Enable Discounts ' . '</label><input type="checkbox" style="float: left; margin-top: 5px;" '.$discount.' id="' . $this->get_field_id('show-discount') . '" name="' . $this->get_field_name('show-discount') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-price') . '">' . 'Enable Prices ' . '</label><input type="checkbox" style="float: left; margin-top: 5px;" '.$price.' id="' . $this->get_field_id('show-price') . '" name="' . $this->get_field_name('show-price') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-color') . '">' . 'Enable Colors ' . '</label><input type="checkbox" style="float: left;margin-top: 5px;" '.$check_color.' id=" ' . $this->get_field_id('show-color') . '" name="' . $this->get_field_name('show-color') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-size') . '">' . 'Enable Sizes ' . '</label><input type="checkbox" style="float: left;margin-top: 5px;" '.$size.' id=" ' . $this->get_field_id('show-size') . '" name="' . $this->get_field_name('show-size') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-brand') . '">' . 'Enable Brands ' . '</label><input type="checkbox" style="float: left;margin-top: 5px;" '.$brand.' id=" ' . $this->get_field_id('show-brand') . '" name="' . $this->get_field_name('show-brand') . '" /></p>';
        echo '<p><label for="' . $this->get_field_id('show-retailer') . '">' . 'Enable Retailers ' . '</label><input type="checkbox" style="float: left;margin-top: 5px;" '.$retailer.' id=" ' . $this->get_field_id('show-retailer') . '" name="' . $this->get_field_name('show-retailer') . '" /></p>';
        ?>

        <?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['show-category'] = esc_attr($new_instance['show-category']);
        $instance['show-discount'] = esc_attr($new_instance['show-discount']);
        $instance['show-price'] = esc_attr($new_instance['show-price']);
        $instance['show-color'] = esc_attr($new_instance['show-color']);
        $instance['show-size'] = esc_attr($new_instance['show-size']);
        $instance['show-brand'] = esc_attr($new_instance['show-brand']);
        $instance['show-retailer'] = esc_attr($new_instance['show-retailer']);
        $instance['choose-category'] = $new_instance['choose-category'];
        return $instance;
    }
}
add_action('widgets_init', 'register_shopstyle_widget');
 function register_shopstyle_widget(){
    register_widget('Shopstyle_widget');
}