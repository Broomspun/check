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
class MyChart_Public {

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

	private  $pdo;

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

        $device_setting = get_option('device_center');

        if($device_setting) {

            $host = trim($device_setting['host']);
            $user = trim($device_setting['user']);
            $pass = trim($device_setting['pass']);
            $db = trim($device_setting['dbname']);
            $charset = 'utf8';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $opt);
        } else
            $this->pdo = null;

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

		wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_register_style('font_awesome_css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('font_awesome_css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mychart-public.css', array(), $this->version, 'all' );

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
        wp_enqueue_script( 'jquery-ui-tabs');
        wp_enqueue_script( 'chart-js','https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');
        wp_enqueue_script( 'chart-js-bundle','https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js');
        wp_enqueue_script( 'chart-js-annotation','https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/0.5.7/chartjs-plugin-annotation.min.js');
        wp_enqueue_script( 'chart-js-utils',plugin_dir_url( __FILE__ ) . 'js/utils.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mychart-public.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_parms', array('ajaxurl' => admin_url('admin-ajax.php')));

	}

    public function myChart_callback($attrs){
	    $aParams = array(
            'title' => 'Tara V.3 (indoor)',
            'multiple' => 0,
            'limit' =>  250,
            'width' => 800,
            'height'=> 400,
            'device_id'=> '',
            'graph_title'=>'pH transient with time',
            'sensor_id'=> '',
            'tick_width'=>10,
            'line_width'=>2,
            'fill'=>1,
            'full_chart'=>1,  //1-full chart,  0- scroll chart
            'showline'=>1,
            'pointradius'=>2,//The radius of the point shape. If set to 0, the point is not rendered.
            'backgroundcolor'=>'#f2f2f2',//The fill color under the line
            'x_showgrid'=>1, //1-show 0-hide
            'y_showgrid'=>1, //1-show 0-hide
            'chart_type'=>'line', //line , bar
            'canvas_width'=>900,
        );
        extract(shortcode_atts($aParams, $attrs));

        $aParams = array(
            'device_id'=> $device_id,
            'sensor_id'=> $sensor_id,
            'limit' =>  $limit,
            'width' => $width,
            'height'=> $height,
            'title' => $title,
            'multiple' => $multiple,
            'graph_title'=>$graph_title,
            'tick_width'=>$tick_width,
            'line_width'=>$line_width,
            'fill'=>$fill,
            'full_chart'=>$full_chart,  //1-full chart,  0- scroll chart
            'showline'=>$showline,
            'pointradius'=>$pointradius,//The radius of the point shape. If set to 0, the point is not rendered.
            'backgroundcolor'=>$backgroundcolor,//The fill color under the line
            'x_showgrid'=>$x_showgrid, //1-show 0-hide
            'y_showgrid'=>$y_showgrid, //1-show 0-hide
            'chart_type'=>$chart_type, //line , bar
            'canvas_width'=>$canvas_width,
        );

        global $my_chart;

        ob_start();

        $device_setting = get_option('device_center');

        if(''==$device_setting){

            echo '<p>You have no permission to device center</p>';
            $output = ob_get_contents(); // end output buffering
            ob_end_clean(); // grab the buffer contents and empty the buffer
            return $output;
        }

        // get user_id of  logged-in user

        $user = get_current_user_id();
        if($user){
            $user = wp_get_current_user();
            $current_user = $user->user_login;
            $current_user = 'leone';

            //get all devices
            $query = 'SELECT device_name, device_id, device_description, airtemp_id, registration_time FROM devices WHERE user_id = "'.$current_user.'"';
            if($device_id!='')
                $query .= sprintf(' AND device_id =%d', $device_id);

            $res = $this->pdo->query($query);
            $all_devices = $res->fetchAll();

            ob_start();

            if(!$all_devices){
                $str = '<b>Tara said:</b> it seems no garden devices was found, <a href="">do you want to start creating one?</a>';
                echo $str;
                $output = ob_get_contents(); // end output buffering
                ob_end_clean(); // grab the buffer contents and empty the buffer
                return $output;
            }

            echo '<h2>'.$title.'</h2>';
            ?>

            <ul class="all-graden-devices-list">

            <?php


            foreach ($all_devices as $device){
                if($aParams['device_id']!='') {
                    $aParams['device-id'] = $aParams['device_id'];
                    unset($aParams['device_id']);
                } else
                    $aParams['device-id'] = $device['device_id'];

                echo '<li><a href="javascript:void(0)" class="garden-device" ';
                $str = '';
                foreach ($aParams as $key=>$value){
                    $str.='data-'.$key.'="'.$value.'" ';
                }
                echo $str.'>'.$device['device_name'];
                echo '<i class="ajax-spin-loading"></i></a><br/>';
                echo '<span class="device-description">'.$device['device_description'].'</span>';
                echo '<div id="my-chart'.$device['device_id'].'-target" class="my-chart"></div>';
                echo '</li>';
            }
            ?>
            </ul>
        <?php
            $output = ob_get_contents(); // end output buffering
            ob_end_clean(); // grab the buffer contents and empty the buffer
            return $output;
        }
        else
            return null;
    }

    //Ajax action
    public function getHistorySensors(){
        $aParms = array(
        'device_id'         => $_POST['device_id'],
        'sensor_id'         => $_POST['sensor_id'],
        'limit'             => $_POST['limit'],
        'width'             => $_POST['width'],
        'height'            => $_POST['height'],
        'title'             => $_POST['$title'],
        'multiple'          => $_POST['$multiple'],
        'graph_title'       => $_POST['graph_title'],
        'tick_width'        => $_POST['tick_width'],
        'line_width'        => $_POST['line_width'],
        'fill'              => $_POST['fill'],
        'full_chart'        => $_POST['full_chart'],
        'showline'          => $_POST['showline'],
        'pointradius'       => $_POST['pointradius'],
        'backgroundcolor'   => $_POST['backgroundcolor'],
        'x_showgrid'        => $_POST['x_showgrid'],
        'y_showgrid'        => $_POST['y_showgrid'],
        'chart_type'        => $_POST['chart_type'],
        'canvas_width'      => $_POST['canvas_width'],
        );

        if(empty($aParms['sensor_id']))
            $all_sensors = $this->getAllHistoriesSensors($aParms['device_id'], $aParms['limit']);
        else
            $all_sensors = $this->getAllHistoriesFromSensor($aParms);

        $data = array(
            'device_id'=>$aParms['device_id'],
            'sensors' => $all_sensors,
        );

        $fill       = ($aParms['fill']==1)?'true': 'false';
        $showline   = ($aParms['showline']==1)?'true': 'false';
        $x_showgrid = ($aParms['x_showgrid']==1)?'true': 'false';
        $y_showgrid = ($aParms['y_showgrid']==1)?'true': 'false';

        $device_id = $aParms['device_id'];
        ob_start();
        ?>
        <script>
            jQuery( function() {
                jQuery( "#tabs-device-<?php echo $device_id; ?>" ).tabs();
            } );
        </script>

        <div id="tabs-device-<?php echo $device_id;?>">
            <ul>
            <?php
              foreach ($all_sensors as $sensor){
                  $shortname = trim($sensor['short_name']);
                  $shortname = str_replace(' ','-', $shortname);
                  echo '<li><a href="#tabs-'.$device_id.'-'.$shortname.'">'.$sensor['full_name'].'</a></li>';
              }
            ?>
            </ul>
            <?php
            $full_chart = $aParms['full_chart'];
            $height_wrapper = (int)$aParms['height']+50;

            foreach ($all_sensors as $sensor){
                $shortname = trim($sensor['short_name']);
                $shortname = str_replace(' ','-', $shortname);
                $chart_wrapper_id = round(microtime(true) * 1000);
                $container_width = (int)$aParms['tick_width']*count($sensor['history']);
                ?>
                <div id="tabs-<?php echo $device_id.'-'.$shortname;?>">
                <?php
                 if($full_chart) {
                ?>
                <div class="mychart-wrapper-<?php echo $chart_wrapper_id; ?>"
                     style="overflow-x: scroll; height: <?php echo $height_wrapper; ?>px;">
                    <div class="chart_container" style="width: <?php echo $container_width; ?>px;">
                        <canvas id="myChart-<?php echo $device_id.'-'.$shortname;; ?>"
                                style="height: <?php echo $aParms['height']; ?>px!important;" width="<?php echo $aParms['canvas_width']; ?>"></canvas>
                    </div>
                </div>
                <?php
            } else {?>
                <div class="mychart-wrapper-<?php echo $chart_wrapper_id; ?>"
                     style="height:<?php echo $aParms['height']; ?>px;">
                    <div class="chart_container" style="width: 100%;">
                        <canvas id="myChart-<?php echo $device_id.'-'.$shortname;?>" style="height: <?php echo $aParms['height']; ?>px!important;" width="<?php echo $aParms['canvas_width']; ?>"></canvas>
                    </div>
                </div>
            <?php
            }
             echo '</div>';
            }
            foreach ($all_sensors as $sensor) {
                $shortname = trim($sensor['short_name']);
                $shortname = str_replace(' ', '-', $shortname);
                $histories = $sensor['history'];
                $annotation_id = round(microtime(true) * 1000);

                $x_values = '[';//time axis
                $y_values = '[';//y axis

                foreach ($histories as $history){
                    $x_values .='"'.$history['time'].'",';
                    $y_values .= $history['value'].',';
                }
                $x_values .= ']';//time axis
                $y_values .= ']';//y axis
                ?>
                <script>
                    jQuery( function() {
                        window.chartColors = {
                            red: 'rgb(255, 99, 132)',
                            orange: 'rgb(255, 159, 64)',
                            yellow: 'rgb(255, 205, 86)',
                            green: 'rgb(75, 192, 192)',
                            blue: 'rgb(54, 162, 235)',
                            purple: 'rgb(153, 102, 255)',
                            grey: 'rgb(201, 203, 207)'
                        };
                        var timeFormat = 'YYYY-MM-DD HH:mm:ss';
                        var ctx = "myChart-<?php echo $device_id.'-'.$shortname; ?>"
                        var myChart = new Chart(ctx, {
                            type: "<?php echo $aParms['chart_type']?>",
                            data: {
                                labels: <?php echo $x_values;?>,
                                datasets: [{
                                    label: 'History with time',
                                    data: <?php echo $y_values;?>,
                                    backgroundColor: "<?php echo $aParms['backgroundcolor'];?>",
                                    borderColor: window.chartColors.red,
                                    pointRadius: "<?php echo $aParms['pointradius'];?>",
                                    fill: <?php echo $fill;?>,
                                    borderWidth: <?php echo $aParms['line_width'];?>,
                                }]
                            },
                            options: {
                                responsive: true,
                                showLines: <?php echo $aParms['showline'];?>,
                                title:{
                                    display: true,
                                    text: <?php echo '"'.$aParms['graph_title'].'"'; ?>
                                },
                                scales: {
                                    layout: {
                                        padding: {
                                            top: 5
                                        }
                                    },
                                    xAxes: [{
                                        type: "time",
                                        time: {
                                            format: timeFormat,
                                            // round: 'day'
                                            tooltipFormat: 'll HH:mm'
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Date'
                                        },
                                        gridLines: {
                                            display:<?php echo $x_showgrid;?>,
                                        }
                                    }, ],
                                    yAxes: [{
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'value'
                                        },
                                        gridLines: {
                                            display:<?php echo $y_showgrid;?>,
                                        }
                                    }]
                                },
                                annotation: {
                        drawTime: 'afterDatasetsDraw',
                        events: ['click'],
                        annotations: [{
                            id: "hline-<?php echo $annotation_id;?>",
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-0',
                            value: <?php echo $sensor['avg'] ?>,
                            borderColor: 'black',
                            borderWidth: 3,
                            label: {
                                backgroundColor: "green",
                                content: "Average Value: <?php echo number_format($sensor['avg'],2,'.','');?>",
                                enabled: true
                            },
                            onClick: function(e) {
                                // The annotation is is bound to the `this` variable
                                console.log('Annotation', e.type, this);
                            }
                        },  ]
                    }
                            }
                        });
                    } );


                </script>
            <?php
            }
            ?>
        </div>

        <?php
        $output = ob_get_contents(); // end output buffering
        ob_end_clean(); // grab the buffer contents and empty the buffer
        $data['html'] = $output;

        echo json_encode($data);
        wp_die();
    }
    /**
     * This function gets the Histories of all sensor registered on a single device from device_id
     *
     * @since    1.0.0
     * @param    int    $device_id       device identifier
     * @param    int    $limit           the number of history to retrieve
     * @return   array  returns unique sensor lists
     */
    private function getAllHistoriesSensors($device_id, $limit){

        $query = 'SELECT datalog.* , dataset.data_nice_name, dataset.data_category, dataset.data_uom, dataset.data_order 
	  FROM datalog JOIN dataset ON datalog.data_id = dataset.data_id 
	  WHERE datalog.entry_id IN (SELECT MAX(datalog.entry_id) from datalog GROUP BY datalog.data_id) AND datalog.device_id = '.$device_id.'
	  ORDER BY dataset.data_order ASC';

        if(is_numeric($limit) && $limit !=-1)
            $query .=' LIMIT '.$limit;

        $res = $this->pdo->query($query);
        $results = $res->fetchAll();

        $all_sensors = array();

        if($results){
            foreach ($results as $sensor){
                $sensor_id = $sensor['data_id'];


                $history = $this->getHistorySensor($device_id, $sensor_id, $limit);
                $end = $history[0]['time'];
                $start = $history[count($history)-1]['time'];

                $criteria = $this->getMaxMinAvg($device_id, $sensor_id, $start, $end);

                $all_sensors[$sensor_id] = array(
                  'short_name'=>$sensor['data_name'],
                  'full_name'=>$sensor['data_nice_name'],
                  'unit'=>$sensor['data_uom'],
                  'category'=>$sensor['data_category'],
                  'history'=>$history,
                  'max'=>$criteria['max'],
                  'min'=>$criteria['min'],
                  'avg'=>$criteria['avg'],
                );
            }
        }

        return $all_sensors;
    }

    private function getAllHistoriesFromSensor($aParms){

        $query = 'SELECT datalog.* , dataset.data_nice_name, dataset.data_category, dataset.data_uom, dataset.data_order 
	  FROM datalog JOIN dataset ON datalog.data_id = dataset.data_id 
	  WHERE datalog.entry_id IN (SELECT MAX(datalog.entry_id) from datalog GROUP BY datalog.data_id) AND datalog.device_id = '.$aParms['device_id'].'
	  ORDER BY dataset.data_order ASC';

        if(is_numeric($aParms['limit']) && $aParms['limit'] !=-1)
            $query .=' LIMIT '.$aParms['limit'];

        $res = $this->pdo->query($query);
        $results = $res->fetchAll();

        $all_sensors = array();

        if($results){
            foreach ($results as $sensor){
                $sensor_id = $sensor['data_id'];
                if($aParms['sensor_id']!='' && $sensor_id!=$aParms['sensor_id']) continue;

                $history = $this->getHistorySensor($aParms['device_id'], $sensor_id, $aParms['limit']);

                $end = $history[0]['time'];
                $start = $history[count($history)-1]['time'];

                $criteria = $this->getMaxMinAvg($aParms['device_id'], $sensor_id, $start, $end);

                $all_sensors[$sensor_id] = array(
                    'short_name'=>$sensor['data_name'],
                    'full_name'=>$sensor['data_nice_name'],
                    'unit'=>$sensor['data_uom'],
                    'category'=>$sensor['data_category'],
                    'history'=>$history,
                    'max'=>$criteria['max'],
                    'min'=>$criteria['min'],
                    'avg'=>$criteria['avg'],
                );
            }
        }

        return $all_sensors;
    }

    /**
     * This function gets the history of a sensor on a single device with device_id
     *
     * @since    1.0.0
     * @param    int    $device_id       device identifier
     * @param    int    $sensor_id       device identifier
     * @param    int    $limit           the number of history to retrieve
     * @return   array  returns history with time of an senosor
     */
    private function getHistorySensor($device_id, $sensor_id, $limit = 50){
        global $wpdb;

        $query = 'SELECT datalog.* , dataset.data_nice_name, dataset.data_uom, devices.device_name, devices.device_id FROM datalog JOIN dataset ON datalog.data_id = dataset.data_id JOIN devices ON dataset.device_id = devices.device_id WHERE datalog.device_id ='.$device_id.' and datalog.data_id='.$sensor_id.' ORDER BY datalog.entry_id DESC';

        if(is_numeric($limit) && $limit !=-1)
            $query .=' LIMIT '.$limit;

        $res = $this->pdo->query($query);
        $results = $res->fetchAll();

        $sensorHistory = array();

        foreach ($results as $history){
            $sensorHistory[] = array(
              'time'=>$history['entry_time'],
              'value'=>$history['value'],
            );
        }

        return $sensorHistory;
    }

    function myChartDetail_callback($attrs){
        extract(shortcode_atts(array(
            'title' => 'Tara V.3 (indoor)',
            'graph_title'=>'pH transient with time',
            'limit' =>  50,
            'width' => 1000,
            'height'=> 400,
            'device_id'=>3,
            'sensor_id'=> 33,
            'tick_width'=>10,
            'line_width'=>2,
            'fill'=>1,
            'full_chart'=>1,  //1-full chart,  0- scroll chart
            'showline'=>1,
            'pointradius'=>4,//The radius of the point shape. If set to 0, the point is not rendered.
            'backgroundcolor'=>'#e35f9c',//The fill color under the line
            'x_showgrid'=>1, //1-show 0-hide
            'y_showgrid'=>1, //1-show 0-hide
            'chart_type'=>'line', //line , bar
            'canvas_width'=>900,
        ), $attrs));

        $fill = ($fill==1)?'true': 'false';
        $showline = ($showline==1)?'true': 'false';
        $x_showgrid = ($x_showgrid==1)?'true': 'false';
        $y_showgrid = ($y_showgrid==1)?'true': 'false';

        // get user_id of  logged-in user

        global $wpdb;

        $user = get_current_user_id();

        if($user) {
            $user = wp_get_current_user();
            $current_user = $user->user_login;
            $current_user="leone";

            ob_start();
            echo '<h2>'.$title.'</h2>';
            echo '<hr>';

            //get sensor properties

            $query = 'SELECT * FROM dataset WHERE device_id='.$device_id.' AND data_id='.$sensor_id;

            $res = $this->pdo->query($query);
            $sensor = $res->fetchAll();

            $shortname = trim($sensor['data_name']);
            $shortname = str_replace(' ','-', $shortname);
            $history = $this->getHistorySensor($device_id,$sensor_id,$limit);

            $all_sensors[$sensor['data_id']] = array(
                'short_name'=>$sensor['data_name'],
                'full_name'=>$sensor['data_nice_name'],
                'unit'=>$sensor['data_uom'],
                'category'=>$sensor['data_category'],
                'history'=>$history
            );

            $height_wrapper = intval($height+50);
            $container_width = $tick_width*count($history);

            $chart_id = round(microtime(true) * 1000);

            if($full_chart) {
                ?>
                <div class="mychart-wrapper-<?php echo $chart_id; ?>"
                     style="overflow-x: scroll; height: <?php echo $height_wrapper; ?>px;">
                    <div class="chart_container" style="width: <?php echo $container_width; ?>px;">
                        <canvas id="myChart-<?php echo $chart_id; ?>"
                                style="height: <?php echo $height; ?>px!important;" width="<?php echo $canvas_width; ?>"></canvas>
                    </div>
                </div>
                <?php
            } else {?>
                <div class="mychart-wrapper-<?php echo $chart_id; ?>"
                     style="<?php echo $height; ?>px;">
                    <div class="chart_container" style="width: 100%;">
                        <canvas id="myChart-<?php echo $chart_id; ?>" style="height: <?php echo $height; ?>px!important;" width="<?php echo $canvas_width; ?>"></canvas>
                    </div>
                </div>
            <?php
            }
            foreach ($all_sensors as $sensor) {
                $shortname = trim($sensor['short_name']);
                $shortname = str_replace(' ', '-', $shortname);
                $histories = $sensor['history'];

                $x_values = '[';//time axis
                $y_values = '[';//y axis
                $i=0;
                foreach ($histories as $history){
                    $x_values .='"'.$history['time'].'",';
                    $y_values .= $history['value'].',';
                    $i++;
                }
                $x_values .= ']';//time axis
                $y_values .= ']';//y axis
                ?>
                <script>
                    jQuery( function() {
                        var timeFormat = 'YYYY-MM-DD HH:mm:ss';
                        var $times=[

                        ];
                        window.chartColors = {
                            red: 'rgb(255, 99, 132)',
                            orange: 'rgb(255, 159, 64)',
                            yellow: 'rgb(255, 205, 86)',
                            green: 'rgb(75, 192, 192)',
                            blue: 'rgb(54, 162, 235)',
                            purple: 'rgb(153, 102, 255)',
                            grey: 'rgb(201, 203, 207)'
                        };

                        var ctx = "myChart-<?php echo $chart_id; ?>"
                        var myChart = new Chart(ctx, {
                            type: '<?php echo $chart_type;?>',
                            data: {
                                labels: <?php echo $x_values;?>,
                                datasets: [{
                                    label: 'History with time',
                                    data: <?php echo $y_values;?>,
                                    backgroundColor: <? echo '"'.$backgroundcolor.'"';?>,
                                    borderColor: window.chartColors.red,
                                    pointRadius: <?php echo $pointradius;?>,
                                    fill: <?php echo $fill;?>,
                                    borderWidth: <?php echo $line_width;?>,
                                }]
                            },
                            options: {
                                responsive: true,
                                showLines: <?php echo $showline;?>,
                                title:{
                                    display: true,
                                    text: <?php echo '"'.$graph_title.'"'; ?>
                                },
                                scales: {
                                    layout: {
                                        padding: {
                                            top: 5
                                        }
                                    },
                                    xAxes: [{
                                        type: "time",
                                        time: {
                                            format: timeFormat,
                                            // round: 'day'
                                            tooltipFormat: 'll HH:mm'
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Date'
                                        },
                                        gridLines: {
                                            display:<?php echo $x_showgrid;?>,
                                        }
                                    }, ],
                                    yAxes: [{
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'value'
                                        },
                                        gridLines: {
                                            display:<?php echo $y_showgrid;?>,
                                        }
                                    }]
                                },
                            }
                        });
                    } );


                </script>
                <?php
            }

            $output = ob_get_contents(); // end output buffering
            ob_end_clean(); // grab the buffer contents and empty the buffer
            return $output;
        }

    }

    private function getMaxMinAvg($device_id, $sensor_id, $start, $end){
        $query = sprintf('SELECT MAX(value) as max1, MIN(value) as min1,AVG(value) as avg1 FROM `datalog` WHERE `entry_time`>= "%s" and `entry_time`<="%s" and `device_id`=%d and `data_id`=%d', $start, $end,$device_id, $sensor_id);

        $res = $this->pdo->query($query);
        $results = $res->fetchAll();
		$criteria = array();
        foreach ($results as $result){
            $criteria['max'] = $result['max1'];
            $criteria['min'] = $result['min1'];
            $criteria['avg'] = $result['avg1'];
            $criteria['query'] = $query;
        }
        return $criteria;
    }

}
