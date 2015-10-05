<!DOCTYPE html>
<html lang="en">

<head>
     <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css">
     <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
</head>

<?php
	require_once('header.php');
	require_once 'couchlib/couch.php';
	require_once 'couchlib/couchClient.php';
	require_once 'couchlib/couchDocument.php';	
	require_once('date_functions.php');
	
	$date = new DateTime();
	$startdate = create_startdate($date);
	$enddate = create_enddate($startdate,48);
	
	//echo $startdate;
	//echo $enddate;
	
	// set a new connector to the CouchDB server
	$client = new couchClient ('http://172.25.1.45:5984','watchcat_stats');
	
	// set a new connect to the couchdb hosts config database
	$couch_hosts_client = new couchClient ('http://172.25.1.45:5984','watchcat_hosts');
	$hostnames = $couch_client->getView('wc_hosts','hostnames');
    
    $servers = array();
    foreach ($hostnames as $rows){
        foreach ($rows as $host){
            $servers[] = $host->key;
        }
    }   

	$i = 0;
	foreach ($servers as $server){
		try {
			$opts = array("startkey" => array($server, $startdate), "endkey" => array($server, $enddate),  "descending" => true);
			$cat_stats = $client->setQueryParameters($opts)->getView('stats','by_server_date');
			$cat_stats_array[] = $cat_stats;
		} catch (Exception $e) {
		   echo "something weird happened: ".$e->getMessage()."<BR>\n";
		}
	

	 echo '<div class="panel-body">';
	 echo '<div class="col-md-6"> </div>';
	 	echo '<div class="panel panel-default">';
	 		echo '<div class="panel-heading">';
				echo '<h3 class="panel-title">'.$server.' CPU Load usage over the last 24 hours</h3>';
	    	echo '</div>';
	    		echo'<div class="panel-body">';
	    			echo'<div id="'.$i.'" style="height: 150px;"></div>';
				echo '</div>';
			echo '</div>';
	 echo '</div>';
	 echo '</div>';
	 $i++;
    }
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>

<?php
	$i = 0;
	foreach ($cat_stats_array as $cat_stats){
?>
<script type="text/javascript">
        
		new Morris.Line({
		  // ID of the element in which to draw the chart.
		  //element: 'cpuchart',
		  element: '<?php echo $i; ?>', 
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: [
		   	<?php 		
			   		foreach($cat_stats->rows as $historic_stats){
						$time =  $historic_stats->value->TimeStamp;
						$json = json_decode($historic_stats->value->metrics);
						echo "{ time: '".$time."' , value: ".$json->load_average." },";
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
<?php
	$i++;
	}
?>