<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css">
<link rel="stylesheet" href="resources/js/morris-0.4.3/morris.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<!-- <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script> -->
<script src="resources/js/raphael-min.js"></script>
<script src="resources/js/morris-0.4.3/morris.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>

</head>
				 
<body>
<div class="container">

	<?php 
	    require_once('header.php'); 
	    require_once 'couchlib/couch.php';
    	require_once 'couchlib/couchClient.php';
        require_once 'couchlib/couchDocument.php';	
    ?>
	       
	
	<div class="page-header">
		 <h1>Watch Cat</a> 
		 <small><?php echo $_GET['server']?> metric status</small></h1>
	</div>

<?php 
	if (isset($_GET['server'])){
			$server = $_GET['server'];
			$statuses[$server] = json_decode(file_get_contents(trim("http://".$server.":4569/status"))) ;
	}else{
		// set a new connect to the couchdb hosts config database
    	$couch_hosts_client = new couchClient ('http://172.25.1.45:5984','watchcat_hosts');
    	//get list of actively monitored hosts
    	$hostnames = $couch_client->getView('wc_hosts','active_hostnames');
        $servers = array();
        foreach ($hostnames as $rows){
            foreach ($rows as $host){
                $servers[] = $host->key;
            }
        }   
        
        //get list if hosts NOT being actively monitored
        $inactive_hostnames = $couch_client->getView('wc_hosts','inactive_hostnames');
        $inactive_servers = array();
        foreach ($inactive_hostnames as $rows){
            foreach ($rows as $host){
                $inactive_servers[] = $host->key;
            }
        } 
        
        //get the metric statuses for all active servers
		foreach ($servers as $server){
			$statuses[$server] = json_decode(file_get_contents(trim("http://".$server.":4569/status"))) ;
		}
		
		//append the inactivbe servers for drawing on the page
		foreach ($inactive_servers as $inactive_server){
    		$statuses[$inactive_server] = NULL;
		}
		

	}
 ?>
	<div class="row well">
	 
		 <div class="col-md-12">
	
	<?php
	$i=1;
	foreach ($statuses as $key => $status){
			$red = "#f55238";
			$green = "#2fcf71";
			$gray = "#dcdcdc";
			
			$colors = array();
			
			if ($status->mem_usage) {
			 	$colors[] = $green;
			 }
			 else {
				 $colors[] = $red;
				 $selected_index = 0;
			 }
			 
			 if ($status->disk_space) {
			 	$colors[] = $green;
			 }
			 else {
				 $colors[] = $red;
				 if (!isset($selected_index)){ $selected_index = 1; }
			 }
			  
			  if ($status->load_average) {
			 	$colors[] = $green;
			 }
			 else {
				 $colors[] = $red;
				 if (!isset($selected_index)){ $selected_index = 2; }				 
			 }
			 
			 if ($status->processes) {
			 	$colors[] = $green;
			 }elseif (is_null($status->processes)){
			 	$colors[] = $gray;
			 }else {
				 $colors[] = $red;
				 if (!isset($selected_index)){ $selected_index = 3; }				 
			 }
			 
			 if(is_null($status->hostname)){
				 $colors[] = $red;
				 $selected_index = 4;
			 }else{
				 $colors[] = $green;
				 if (!isset($selected_index)){ $selected_index = 4; }	 
			 }
			 
			 //set to gray for inactive servers
			 if (in_array($key, $inactive_servers)){
    			 unset($colors);
    			 $colors = array($gray, $gray, $gray, $gray);
       			 $selected_index = 4;
			 }
			 
			 //if nothing was wrong set the selected index to 4 - healthy;
			$donutid = "donut".$i; 
	?>
		<div class="col-md-3">
		    <div class="graph-container">
		      <div class="h3"><center><a href="metrics.php?server=<?php echo $key; ?>"><?php echo $key; ?></a></center></div>
				<div id=<?php echo $donutid; ?> style="height: 175px;"></div>
				<script>
				
				
				var <?php echo $donutid; ?> = new Morris.Donut({
				  // ID of the element in which to draw the chart.
				  element: <?php echo $donutid; ?>,
				  // Chart data records -- each entry in this array corresponds to a point on
				  // the chart.
				  data: [
					    {label: "Memory", value: 20},
					    {label: "Disk", value: 20},
					    {label: "CPU Load", value: 20},
					    {label: "Processes", value:20},
					    {label: "Reachable", value:20}
					  ], 
				  colors: <?php echo json_encode($colors); ?> , 
				  formatter:  function (y, data) { return "<?php echo strtoupper($key); ?>" }
				  
				});			 
				
				 <?php echo $donutid; ?>.select(<?php echo $selected_index; ?>);					
				</script>
			    </div>
			</div>
		 <?php
		 $i++;
		 unset($selected_index);
		 

	 }
	
	?>
	    </div> <!-- /col-span -->

	</div> <!-- /row -->

</div> <!-- /container -->

</body>
</html>