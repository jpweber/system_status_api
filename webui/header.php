<div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
        <div class="navbar-header">
        <meta http-equiv="refresh" content="600" >
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://watchcat.bluetonecommunications.com">Watch Cat</a>
        </div>
        <div class="collapse navbar-collapse">
           <ul class="nav navbar-nav">
	        <li><a href="http://watchcat.bluetonecommunications.com/cpu_load.php">CPU Load</a></li>
	        <li><a href="http://watchcat.bluetonecommunications.com/alerts.php">Alerts</a></li>

        
          <?php
            require_once 'couchlib/couch.php';
        	require_once 'couchlib/couchClient.php';
            require_once 'couchlib/couchDocument.php';	
            // set a new connector to the CouchDB server
        	$couch_client = new couchClient ('http://172.25.1.45:5984','watchcat_hosts');
        	$hostnames = $couch_client->getView('wc_hosts','hostnames');
             
            echo'<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Servers<b class="caret"></b></a><ul class="dropdown-menu">';

            foreach ($hostnames as $rows){
                         foreach ($rows as $host){
        	        echo "<li><a href='metrics.php?server=".$host->key."'>" .$host->key."</a></li>";
        	        }
        	
        	}   

         ?>
         
          </ul>
   
          <ul class="nav navbar-nav navbar-right">
          
          </ul>
        </div><!--/.nav-collapse -->
        <style> body { padding-top: 70px; } </style>
      </div>