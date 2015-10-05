<!DOCTYPE html>
<html lang="en">

<head>
     <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css">
     <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
	 <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
	 <script type="text/javascript" src="bootstrap/js/bootstrap-tooltip.js"></script>
</head>

<div class="container">
<?php require_once('db_connect.php'); ?>
<?php require_once('header.php'); ?>
	
	<div class="page-header">
        <h1>Alerts<small> Current and Historic</small></h1>
	</div>
	
	<?php
	
		$dbclient = db_connect("webmysql01", "watchcat", "Phu5Ar7Od", "watchcat");
		//var_dump($dbclient);
		
		$query = "SELECT `server`, `metric`, `description`, `created`, `updated`, `last_seen`, `cleared` FROM alerts ORDER BY `updated` DESC";
		//var_dump($query);
		$results = $dbclient->query($query);
		
		if ($result = $dbclient->query($query)) {
			//we have results start the table
			?>
			     
			<div class="col-md-12">
        
      			<table class="table table-striped table-hover">
	            	<thead>
						<tr>
							<th>Server</th>
		                    <th>Metric</th>
		                    <th>Description</th>
		                    <th>Created</th>
		                    <th>Updated</th>
		                    <th>Last Seen</th>
		                    <th>Cleared</th>
						</tr>
	            	</thead>
	            	<tbody>
	        
	        <?php
		    /* fetch object array */
		    while ($obj = $result->fetch_object()) {
		    
		    	if ($obj->cleared == 0){
		    		echo "<tr class='danger'>";
		    	}else{
			    	echo "<tr>";
		    	}
		    	
		    	echo "<td>";
		    	echo '<a href="metrics.php?server='.strtolower($obj->server).'">'.$obj->server.'</a>';
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	echo $obj->metric;
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	echo $obj->description;
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	echo date('Y-m-d h:i:s',$obj->created);
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	echo date('Y-m-d h:i:s',$obj->updated);
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	echo date('Y-m-d h:i:s',$obj->last_seen);
		    	echo "</td>";
		    	
		    	echo "<td>";
		    	if ($obj->cleared == 1){
			    	echo "<span class='glyphicon glyphicon-ok'></span>";
		    	}else{
			    	echo "<span class='glyphicon glyphicon-fire'></span>";	
		    	}
				
		    	echo "</td>";
		    	
				echo "</tr>";
/* 		        printf ("%s (%s)\n", $obj->server, $obj->description); */
		    }
		
		    /* free result set */
		    $result->close();
		}

		//if we failed show the mysql error
		if (!$results) {
		    echo "Failed to run query: (" . $mysqli->errno . ") " . $mysqli->error;
		}

		
		
	?>
