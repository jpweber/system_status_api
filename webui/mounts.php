<!DOCTYPE html>

<html lang="en">

<head>
     <link rel="stylesheet" href="../bootstrap/css/bootstrap.css" type="text/css">
     <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
</head>


<?php 
	require_once 'couchlib/couch.php';
	require_once 'couchlib/couchClient.php';
	require_once 'couchlib/couchDocument.php';
	require_once('date_functions.php');
	require_once('header.php');
	
	$date = new DateTime();
	$startdate = create_startdate($date);
	$enddate = create_enddate($startdate,24);
	
	
	$client = new couchClient ('http://172.25.1.45:5984','watchcat_stats');
	
	$server = $_GET['server'];
	$status = json_decode(file_get_contents(trim("http://".$server.":4569/metrics"))) ;
	
	try {
		$opts = array("startkey" => array($server, $startdate), "endkey" => array($server, $enddate), "limit" => 48 , "descending" => true);
		$cat_stats = $client->setQueryParameters($opts)->getView('stats','by_server_date');
	} catch (Exception $e) {
	   echo "something weird happened: ".$e->getMessage()."<BR>\n";
	}


	if (isset($_GET['mount'])){
		$pre_mount = $_GET['mount'];
		$statuses[$server] = json_decode(file_get_contents(trim("http://".$server.":4569/status")));
	}
			
	if (isset($_GET['server'])){
		$server = $_GET['server'];
		$statuses[$server] = json_decode(file_get_contents(trim("http://".$server.":4569/status")));
	}
	
	$mount = str_replace(".", "/", $pre_mount);
	$json = json_decode($historic_stats->value->metrics);
	$disk = $json->disk_space->mount_point->$mount;
						
?>

<div class="panel-body">
	<div class="col-md-6"> 
		<div class="panel panel-default">
			<div class="panel-heading">
			    <h3 class="panel-title"><?php echo $mount ?> Disk usage over the last 12 hours</h3>
			</div>
			<div class="panel-body">	
				<div id="diskchart" style="height: 150px;"></div>
			</div>
		</div>
	</div>
</div>


<! -- load remote js files -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script>


<script type="text/javascript">

//javascript to draw the graphs
		new Morris.Line({
		  // ID of the element in which to draw the chart.
		  element: 'diskchart',
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: [	
		  <?php			 
		  		foreach($cat_stats->rows as $historic_stats){
					$time =  $historic_stats->value->TimeStamp;
					$json = json_decode($historic_stats->value->metrics);
					foreach ($json->disk_space as $mount_entry){
						if ($mount_entry->mount_point == $mount){
							$percent_used = $mount_entry->percent_used;
							$hd_percentage = number_format($percent_used, 2, '.', '');
							echo "{ time: '".utc_to_eastern($time)."' , value: ".$hd_percentage." },";
						}
					}
					
			}
			
			?>
			
			
			/*
<?php foreach($cat_stats->rows as $historic_stats){
					$json = json_decode($historic_stats->value->metrics);
					if ($json->disk_space->mount_point == $mount){ 
						$disk = $json->disk_space->percent_used;
					}else{
						$disk = "0";
					}
					$time =  $historic_stats->value->TimeStamp;
					echo "{ time: '".$time."' , value: ".$disk." },";
				}
			?> 	   		 
*/
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