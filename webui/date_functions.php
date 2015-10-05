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
function create_enddate($startdate,$hours){
	
	$date = new DateTime($startdate);
	/* $date->setTimezone(new DateTimeZone('UTC')); */
	
	$endkey = $date->modify('-'.$hours. 'hours');
	return  $endkey->format('Y-m-d H:i');
}

function utc_to_eastern($date){
	$utcdate = $date;
	//create new dattime object base on the input data and set timezone to UTC
	$date = new DateTime($utcdate,new DateTimeZone('UTC'));
	//change timezone to eastern
	$date->setTimezone(new DateTimeZone('America/New_York'));
	//create date string with new eastern datetime
	$est_date = $date->format('Y-m-d H:i');
	
	return $est_date;
	
}

?>