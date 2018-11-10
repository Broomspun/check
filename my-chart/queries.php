
<?php 
	/* 2nd November, 2017
		Good morning EVAN. My name is Leone, I am from Italy and I currently live and work in an Africa country called Djibouti.
		Despite I reckon it doesn't make any sense to talk about copyright, I hope you will be respectful and reserved about my project. 
		In fact this is my personal project, and I've been spending years of my life on studying coding during my free time. I decided
		then to invest a little bit on it. I want to make clear that in general I don't jump from a collaborator to another one,
		so what I'm looking for is a rather loyal collaboration that may endure trough the time. 
		
		Going back to work now let me explain you:
		The database assumes that there are a list of devices each of them equipped with sensors.
		
		devices are registered in the devices table
		sensors are registeresd in data_set table
		users are registered in dadabik_users tables
		
		The Relationship between tables is: devices and dataset are sharing device_id key
		devices and dadabik_users are sharing user_id key
		
		In the real life devices are sending sensor values to the webapplication, which stores values in the datalog table.
		
		Our project today is to be able to display these data series from sensors in the wordpress. 
		Please follow the logic below and the comments until the end, especially the last 2 comments to start with fixed values. 
		
		The logic below explains you how I am getting the data do display info like the screenshot I sent you. 
		This logic is an abstract of the functions.php child theme I've been working on.
		
		P.S.
		I know, a plugin would be better. If you think you can help me on that too... let me know, but I reckonb that should be
		subject for another project.
		
		Have a good day,
		Leone
	
	?>



<?php
	
/* CONNECTION TO THE EXTERNAL DB START */
if (isset($_REQUEST['id'])) {
	   $device_id = stripslashes($_REQUEST['id']);
	   }


	define("MYSQL_SERVER", 'localhost');



	define("MYSQL_USER", 'user');



	define("MYSQL_PASSWORD", 'password');



	define("MYSQL_DATABASE", 'db_name');



	//$connect =  new mysqli(MYSQL_SERVER,MYSQL_USER,MYSQL_PASSWORD,MYSQL_DATABASE) or die ("I cannot connect to the database because 1: " . mysql_error());


    $host = MYSQL_SERVER;
    $db   = MYSQL_DATABASE;
    $user = MYSQL_USER;
    $pass = MYSQL_PASSWORD;
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $opt);



// [...] JS is set in the footer here
// add_action('wp_footer', 'my_action_javascript_tara_get_user_devices', 99)


//[...] JS function initiatin AJAX request


//PHP callback function with the queries


// Query retrieving devices belonging to logged in wp_user 
function get_user_devices () {

 global $pdo;

    //$table = 'wp_options';

    //$column = '*';

    $user = wp_get_current_user();

	$current_user = $user->user_login;

    //$current_user = 'leone';

	$query = 'SELECT device_name, device_id, device_description, airtemp_id, registration_time FROM devices WHERE user_id = "'.$current_user.'"';

   $res = $pdo->query($query);
    $result = $res->fetchAll();

    if ((!$result) OR ($result == NULL)){
	    $str = '<b>Tara said:</b> it seems no garden devices was found, <a href="">do you want to start creating one?</a>';
	    print(json_encode($str));
	    wp_die();
    }

	foreach ($result as $row)
	{
		$devices[] = array(
						  'device_name' => $row['device_name'],
						  'device_id' => $row['device_id'],
						  'device_description' => $row['device_description'],
						  'airtemp_id'=> $row['airtemp_id'],
						  'registration_time' => $row['registration_time']
						  );
	}
	
	$deviceCount = count($devices);

	$str = '<!-- open -->
	<ul class="collection with-header"><li class="collection-header">
	<h5>Your Garden Devices</h5></li>';

	for ($v = 0; $v <$deviceCount; $v ++) {
						$device_url = add_query_arg( 'id', $devices[$v]['device_id'], site_url( '/garden/?' ));
						$str .= '<li class="collection-item">';
						$str .= '<a href="'.$device_url.'">';
						$str .= $devices[$v]['device_name'];
						$str .= '</a>';
						$str .= '<br>'.$devices[$v]['device_description'];

	}
		$str .= '</ul><!-- close -->';

		print(json_encode($str));

	wp_die();
	}
	
// NOW we have a list that is displaying all user devices, including their ID. When user clicks it we get all data_logs concerning that device:
// for example: 
// This query gets the list of all sensor registered on a single device, and displays the last updated log (of every sensor). 

    $sql = 'SELECT datalog.* , dataset.data_nice_name, dataset.data_category, dataset.data_uom, dataset.data_order 
	  FROM datalog JOIN dataset ON datalog.data_id = dataset.data_id 
	  WHERE datalog.entry_id IN (SELECT MAX(datalog.entry_id) from datalog GROUP BY datalog.data_id) AND datalog.device_id = "'.$device_id.'"
	  ORDER BY dataset.data_order ASC';


//while this one gets the generic list of the latest 20 logged data for that specific device. 

	$query = 'SELECT datalog.* , dataset.data_nice_name, dataset.data_uom, devices.device_name, devices.device_id FROM datalog
				JOIN dataset ON datalog.data_id = dataset.data_id
				JOIN devices ON dataset.device_id = devices.device_id
				WHERE datalog.device_id = "'.$device_id.'" ORDER BY datalog.entry_id DESC LIMIT 20';

// As you can see from any of these example queries I am retrieving the data_id, so from here we can start queries to display graphic data series for a specific data_it. E.G. the simple following one:
 	
 	$query = 'SELECT * from datalog WHERE data_id = "'.$device_id.'" ORDER BY entry_id DESC LIMIT 50';
 	
// With reference to the screen shot I sent you, for example I would like to have a page for a specific device, displaying a line chart for every sensor that is connected to it (dataset.device_id is the common key)
	

// good user on which try the queries are leone and fabio
// good device_id on which run the queries are 3, 37 and 39
