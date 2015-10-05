<!DOCTYPE html>
<html lang="en">

<head>
     <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css">
     <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="resources/js/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.3/morris.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap-tooltip.js"></script>
</head>

<?php
	require_once 'couchlib/couch.php';
	require_once 'couchlib/couchClient.php';
	require_once 'couchlib/couchDocument.php';
	
	require_once('date_functions.php');
	
	
	// set a new connector to the CouchDB server server stats
	$client = new couchClient ('http://172.25.1.45:5984','watchcat_stats');	
	
	$server = $_GET['server'];
	$status = json_decode(file_get_contents(trim("http://".$server.":4569/metrics"))) ;
	$health = json_decode(file_get_contents(trim("http://".$server.":4569/status"))) ;
	
	//get stats from couchdb for $server (name)
	//but first create dates to go in our query
	$date = new DateTime();
	$startdate = create_startdate($date);
	//echo $startdate."<br>";
	$enddate = create_enddate($startdate,24);
	//echo $enddate."<br>";
	try {
		$opts = array("startkey" => array($server, $startdate), "endkey" => array($server, $enddate), "limit" => 24, "descending" => true);
		$cat_stats = $client->setQueryParameters($opts)->getView('stats','by_server_date');
	} catch (Exception $e) {
	   echo "something weird happened: ".$e->getMessage()."<BR>\n";
	}
	
	// Remove decimal places
	$formatted_avg= number_format($status->disk_space[0]->percent_used, 2, '.', '');
	
	#echo $formatted_avg;
	
	$time = $status->uptime; // time duration in seconds
		
	$days = floor($time / (60 * 60 * 24));
	$time -= $days * (60 * 60 * 24);
		
	$hours = floor($time / (60 * 60));
	$time -= $hours * (60 * 60);
		
	$minutes = floor($time / 60);
	$time -= $minutes * 60;
		
	$seconds = floor($time);
	$time -= $seconds;
		
?>


<div class="container">
	
	<?php require_once('header.php'); ?>
	
	<div class="page-header">
        <h1><?php echo strtoupper($status->hostname); ?><small> Metrics Details</small></h1>
	</div>
	
	<div class="row">
	<div  id="example">
	<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-cog"></span></button>
	<script>
	var buttons = 'CPU:  <div class="btn-group" data-toggle="buttons"> '+
											   
												  '<label class="btn btn-primary"><input type="radio" name="options" id="option1"> On'+		
												  '</label>'+													 			
												  '<label class="btn btn-primary">'+							 			
												    '<input type="radio" name="options" id="option2"> Off'+			
												  '</label>'+	  
								              '</br></div></br>Memory: '+
								              '  <div class="btn-group" data-toggle="buttons">'+
											   
												  '<label class="btn btn-primary"><input type="radio" name="options" id="option1"> On'+		
												  '</label>'+													 			
												  '<label class="btn btn-primary">'+							 			
												    '<input type="radio" name="options" id="option2"> Off'+			
												  '</label>'+	  
								              '</div></br>Load:'+
								              '  <div class="btn-group" data-toggle="buttons">'+
											   
												  '<label class="btn btn-primary"><input type="radio" name="options" id="option1"> On'+		
												  '</label>'+													 			
												  '<label class="btn btn-primary">'+							 			
												    '<input type="radio" name="options" id="option2"> Off'+			
												  '</label>'+	  
								              '</div></br>Disk:   '+
								              '  <div class="btn-group" data-toggle="buttons">'+
											   
												  '<label class="btn btn-primary"><input type="radio" name="options" id="option1"> On'+		
												  '</label>'+													 			
												  '<label class="btn btn-primary">'+							 			
												    '<input type="radio" name="options" id="option2"> Off'+			
												  '</label>'+	  
								              '</div>'
								              ;

   $("#example").popover({
        placement: 'auto left',
        html: 'true',
        title : '<span class="text-info"><strong>Toggle Notifications </strong></span>'+
                '<button type="radio" id="close" class="close" onclick="$(&quot;#example&quot;).popover(&quot;hide&quot;);">&times;</button>',
        content :buttons,
        
        
        
        
        

					
        
        	     
    });
	</script>
	</div>
	   	    <!-- Top summary table -->
	        <div class="col-md-12">
        
        				<table class="table">
	            <thead>
                <tr>
                    <th>Server</th>
                    <th>Uptime</th>
                    <th>Memory</th>
                    <th>CPU Load Avg</th>
                    
                    <?php if(isset($status->message_queue)){
	                   		echo "<th>Ready Messages</th>"; 
                    }?>
                </tr>
            </thead>
				<tbody>
				<tr>
	                <td>
	                	<span class="h3">
		                	<span class="text-primary">
			                	<?php echo $status->hostname; ?>
		                	</span>
	                	</span>
                	</td>
	                <td>
	                	<span class="h3">
            		<?php 
            			if ($health->uptime){
            				echo '<span class="text-primary">';
                		}else{
							echo '<span class="text-danger">';  
                		}
	              
                        $time = array($days,$hours,$minutes,$seconds) ;
	                							
						switch($time){
                            case ($time[0] > 0):
                                echo $time[0]." days" ;
                                break; 
                            case ($time[1] > 0):
                                echo $time[1]." hours" ;
                                break; 
                            case ($time[2] > 0):
                                echo $time[2]." minutes" ;
                                break; 
                            case ($time[3] > 0):
                                echo $time[3]." seconds" ;
                                break; 
                            
                        }                              	               
	                ?>
	                		</span>
	                	</span>
	                </td> 
	                <td>
	                	<span class="h3">
		                	 <?php if ($health->mem_usage){
					                	echo '<span class="text-primary">'.$status->mem_usage."%" ;
					               }else{
					               		echo '<span class="text-danger">'.$status->mem_usage."%" ;
					               }
					         ?>
							 </span>
						</span>
					</td>	
					<td> 
						<span class="h3"> 
			                 <?php if ($health->load_average){
						                 echo '<span class="text-primary">'.$status->load_average.'</span></span></td>';
			                 		}else{
								 		echo '<span class="text-danger">'.$status->load_average.'</span></span></td>';
			                 		}
	                 		?>
							</span>
						</span>
					</td>
					<td> 
						<span class="h3"> 
			                 <?php if ($health->message_queue){
						                 echo '<span class="text-primary">'.$status->message_queue[0]->total_ready.'</span></span></td>';
			                 		}else{
								 		echo '<span class="text-danger">'.$status->message_queue[0]->total_ready.'</span></span></td>';
			                 		}
	                 		?>
							</span>
						</span>
					</td>
				</tr>
            </tbody>
			</table>
        </div> <!-- /col span -->
        
                 <!-- graphs -->  
           <div class="panel-group" id="accordion">     
	         	<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
						    <h3 class="panel-title">
							    <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Graphs</a>
						     </h3>
						</div>
						
						<div id="collapseOne" class="panel-collapse collapse in">
							<div class="panel-body">
									<div class="col-md-6"> 
										<div class="panel panel-default">
											<div class="panel-heading">
											    <h3 class="panel-title">Memory Load AVG Last 4 Hours</h3>
											</div>
											<div class="panel-body">	
												<div id="memorychart" style="height: 150px;"></div>
											</div>
										</div>
									</div>
								
							
							
								<div class="col-md-6"> 
									<div class="panel panel-default">
										<div class="panel-heading">
										    <h3 class="panel-title">CPU Load AVG Last 4 Hours</h3>
										</div>
										<div class="panel-body">
											<div id="cpuchart" style="height: 150px;"></div>
										</div>
									</div>
								</div> <!-- /newcolmd5 -->
							</div> <!-- /panel-body -->
						</div> <!-- /collapseOne -->	
					</div> <!-- /panel-defailt -->
	         	</div> <!-- /col-md-10 -->
           </div> <!-- panel group -->
     
		<! -- /graphs -->
        
				    
			<div class = "col-md-12">
				<h3>Processes</h3>
				<div class="progress">
				
			
	         <?php
	       
		        $proc_count = count($status->processes);
		        $proc_percent = 100/$proc_count ;   
		        
		            	foreach($status->processes as $process){
			               		foreach ($process as $key => $value){

									if($value){
										echo'<div class="progress-bar progress-bar-success" style="width:'.$proc_percent.'%">';
										echo '<span>'.strtoupper($key).'</span>';
										echo '</div>';	
									}else{
										echo'<div class="progress-bar progress-bar-danger" style="width:'.$proc_percent.'%"><span>'.strtoupper($key).'</span></div>';	
									} 

								}
						}
						
				

						/*
$variable = array(1,2,3,4,5);
						
						foreach ($variable as $key => $value) {
						               echo'<div class="progress-bar progress-bar-success" style="width:20%">';
							
						}
											
*/	
		     ?>
				
				</div> <!-- progress  -->
			</div> <!-- col mid 12 -->
		
					
		

		
		
			<!-- Mounts -->
	        <div class="col-md-12">
			<h3>Mounts</h3>
			 <table class="table table-bordered table-striped table-condensed">
	            <thead>
	                <tr>
	                	<th>Mount</th>
	                 	<th>Total</th>
	                    <th>Used</th>
	                    <th>Available</th>
	                    <th>Percent Used</th>
	                </tr>
	            </thead>
				
	            <tbody>
		            <?php 
		            $hd_percentage = number_format($disk_metric->percent_used, 2, '.', '');
						foreach($status->disk_space as $disk_metric){
								$mount = str_replace("/",".",$disk_metric->mount_point);
								echo "<tr><td><a href='mounts.php?mount=$mount&server=$server'>".$disk_metric->mount_point."</a></td>";
								
								if ($disk_metric->total < 1024) {
								echo "<td>".($disk_metric->total)." MB</td>";
								}
									else { echo "<td>".number_format($disk_metric->total/1024,2, '.', '')." GB</td>"; 
										
									}
								if ($disk_metric->total - $disk_metric->free < 1024){
									echo "<td>".($disk_metric->total - $disk_metric->free)." MB</td>";
								}
									else {
										echo "<td>".number_format( (($disk_metric->total - $disk_metric->free)/1024 ),2, '.', '')." GB</td>";
									
									 }
	
								if ($disk_metric->free < 1024) {
									echo "<td>".($disk_metric->free)." MB</td>";
								}
									else {
										echo "<td>".number_format($disk_metric->free/1024,2, '.', '')." GB</td>";
									
									 }
							#echo "<td>".($disk_metric->total/1024)."</td></tr>";
								#echo "<td>".($disk_metric->total)."</td>"
								 $hd_percentage = number_format($disk_metric->percent_used, 1, '.', '');
										#echo "<td>".$hd_percentage."%</td></tr>";
										
										echo"<td><div class='progress progress-striped'>";
										if ($hd_percentage > 20) {
												echo'<div class="progress-bar progress-bar-success" style="width:'.$hd_percentage.'%"><span>'.$hd_percentage.'%</span></div>';
										}else {
											echo'<div class="progress-bar progress-bar-success" style="width:'.$hd_percentage.'%"></div>';

										}
											
											echo'</td></tr></div>';
						}
		            ?>
	            
	            </tbody>
			 </table>
			 </div> <!-- /col span -->

            
	</div> <!-- /row -->     
</div> <!-- /contiainer -->


<! -- prep for tabs -->

<! -- load remote js files -->


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#tabs').tab();
    });
    


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
					echo "{ time: '".utc_to_eastern($time)."' , value: ".$load_avg." },";
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
		
		
		new Morris.Line({
		  // ID of the element in which to draw the chart.
		  element: 'cpuchart',
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: [
		   	<?php foreach($cat_stats->rows as $historic_stats){
					$json = json_decode($historic_stats->value->metrics);
					$load_avg = $json->load_average;
					$time =  $historic_stats->value->TimeStamp;
					echo "{ time: '".utc_to_eastern($time)."' , value: ".$load_avg." },";
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


</html>