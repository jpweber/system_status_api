<?php

require_once 'couchlib/couch.php';
require_once 'couchlib/couchClient.php';
require_once 'couchlib/couchDocument.php';
require_once('date_functions.php');

$requestURI = explode('/', $_SERVER['REQUEST_URI']);
//$scriptName = explode('/',$_SERVER['SCRIPT_NAME']);

//make sure we have a good auth token before we can continue
if ($_SERVER['HTTP_X_WATCHCAT_AUTHORIZATION'] != "<token>"){
	 header("HTTP/1.1 403 Unauthorized" );
	 exit;
}

//var_dump($requestURI);
//var_dump($scriptName);

$command = $requestURI[3];
if ($command == "register"){
	// if we are registering a device index 4 is the uuid
	$uuid = $requestURI[4];
	$device_name = $requestURI[5];
}

// if we are not registering a device and just getting status
// index 4 is the server name
$requested_server = $requestURI[4];
// set a new connect to the couchdb hosts config database
$couch_hosts_client = new couchClient ('http://<hostname>:5984','watchcat_hosts');
$hostnames = $couch_hosts_client->getView('wc_hosts','active_hostnames');

$servers = array();
foreach ($hostnames as $rows){
    foreach ($rows as $host){
        $servers[] = $host->key;
    }
}

$inactive_hostnames = $couch_hosts_client->getView('wc_hosts','inactive_hostnames');

$inactive_servers = array();
foreach ($inactive_hostnames as $rows){
    foreach ($rows as $host){
        $inactive_servers[] = $host->key;
    }
}
   
//function to query the watchcat api on a server
function api_query($command, $server){
	$status = json_decode(file_get_contents(trim("http://".$server.":4569/$command")));
	return $status;
}

//function to connect to mysql database
function db_connect($hostname, $user, $pass, $database){
	$mysqli = mysqli_connect($hostname, $user, $pass, $database);
	if ($mysqli->connect_errno) {
	    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
	    return false;
	}
	
	return $mysqli;
}


function register_device($uuid, $device_name){
	$dbclient = db_connect("host", "user", "password", "database");
	$query = "INSERT INTO `devices` (`id`, `uuid`, `device_name`, `timestamp`) VALUES ('', '$uuid', '$device_name', '')";
		
	$results = $dbclient->query($query);
	
	if($results){
		return true;
	}else{
		return false;
	}
}

function couch_query($server){
	$date = new DateTime();
	$startdate = create_startdate($date);
	$enddate = create_enddate($startdate);
	
	$client = new couchClient ('http://<hostname>:5984','watchcat_stats');
	
	try {
		$opts = array("startkey" => array($server, $startdate), "endkey" => array($server, $enddate), "limit" => 12 , "descending" => true);
		$cat_stats = $client->setQueryParameters($opts)->getView('stats','by_server_date');
	} catch (Exception $e) {
	   echo "something weird happened: ".$e->getMessage()."<BR>\n";
	}
	
	return $cat_stats;
	
}

function  set_monitor_status($server, $status){
    $client = new couchClient ('http://<hostname>:5984','watchcat_hosts');
    $opts = array("key" => $server);
    $pre_doc = $client->setQueryParameters($opts)->getView('wc_hosts', 'by_hostname');
 
    $doc = $client->getDoc($pre_doc->rows[0]->id);
    
    $doc->monitor_active = $status;
    try {
        $response = $client->storeDoc($doc);
    }catch (Exception $e){
        echo "something weird happened: ".$e->getMessage()."<BR>\n";
    }
    
    var_dump($response);

}

//only query the api of the specified server
if ($requested_server != ""){
	if ($command == "register"){
		//register the device for push notifications
		$response = register_device($uuid, $device_name);
		
	}elseif($command == "history"){
		$response = couch_query($requested_server);
		
	}elseif($command == "happy"){
		//include the servname in the array for happy requests
		$response[$requested_server] = api_query($command, $requested_server);
		
    }elseif($command == "disable"){
        $response = set_monitor_status($requested_server, false);
        
	}elseif($command == "enable"){
        $response = set_monitor_status($requested_server, true);
        
    }else{
		//otherwise we know the server and just want the data
		$response = api_query($command, $requested_server);
	}
}else{
	//no serer specified, loop through all servers and get all results
	foreach($servers as $server){
		$response[$server] = api_query($command, $server);
	}
	
	//append the inactive servers
	foreach ($inactive_servers as $server){
	    $response[$server] = 2;
	}
}

// json encode the response and return it back to client
header('Content-type: application/json');
echo json_encode($response);



?>
