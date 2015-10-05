<?php

//function to create date for startkey in couch query for stats
function create_startdate($date){
	
	$date->setTimezone(new DateTimeZone('UTC'));

	$minutes =  $date->format('i');
	if ($date->format('i') < 30){
	        //I should round down to 0
	        $startkey = $date->modify('-'.$minutes.' minutes');
	        return $startkey->format('Y-m-d H:i');
	
	}else{
			//round up to 30
			$diff = 30 - $minutes;
			$startkey = $date->modify('+'.$diff.' minutes');
	        return $startkey->format('Y-m-d H:i');

	}
	
}

//function to create date for endkey in couch query for stats
//end date is expected to recieve the results of create date
function create_enddate($startdate){
	
	$date = new DateTime($startdate);
	$date->setTimezone(new DateTimeZone('UTC'));
	
	$endkey = $date->modify('-12 hours');
	return  $endkey->format('Y-m-d H:i');
}

?>