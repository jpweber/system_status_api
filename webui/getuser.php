<!DOCTYPE html>
<html lang="en">

<head>
     <link rel="stylesheet" href="../bootstrap/css/bootstrap.css" type="text/css">
     <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
</head>

<?php
$q = intval($_GET['q']);
echo 'color';
	require_once 'couchlib/couch.php';
	require_once 'couchlib/couchClient.php';
	require_once 'couchlib/couchDocument.php';
	require_once('date_functions.php');
	
	
	// set a new connector to the CouchDB server
	$client = new couchClient ('http://172.25.1.45:5984','watchcat_stats');

$interval = $q;
	//get stats from couchdb for $server (name)
	//but first create dates to go in our query
	$date = new DateTime();
	$startdate = create_startdate($date);
	$enddate = create_enddate($startdate,$interval);
	var_dump($interval);
	
	
	try {
		$opts = array("startkey" => array('app11', $startdate), "endkey" => array($server, $enddate), "limit" => 24, "descending" => true);
		$cat_stats = $client->setQueryParameters($opts)->getView('stats','by_server_date');
	} catch (Exception $e) {
	   echo "something weird happened: ".$e->getMessage()."<BR>\n";
	}


?>
<body>

<div id="memorychart" style="height: 150px;"></div>
</body>
<! -- load remote js files -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>

<script type="text/javascript">
  jQuery(document).ready(function ($){
	  $('#tabs').tab();
  };
    


	 //javascript to draw the graphs
		new Morris.Line({
		  // ID of the element in which to draw the chart.
		  element: 'memorychart',
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: [				 
			<?php foreach($cat_stats->rows as $historic_stats){
					$json = json_decode($historic_stats->value->metrics);
					$load_avg = $json->mem_usage;
					$time =  $historic_stats->value->TimeStamp;
					echo "{ time: '".$time."' , value: ".$load_avg." },";
				}
			?> 	   		 
			  ],
		  // The name of the data record attribute that contains x-values.
		  xkey: 'time',
		  // A list of names of data record attributes that contain y-values.
		  ykeys: ['value'],
		  // Labels for the ykeys -- will be displayed when you hover over the
		  // chart.
		  labels: ['Value']
		});
		
		
		</script>

