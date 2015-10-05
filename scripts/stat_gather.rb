#!/usr/bin/env ruby

require 'open-uri'
require 'json'
require 'couchrest'
require 'parseconfig'

config = ParseConfig.new('/etc/watchcat/server_mon.conf')
servers = config.get_groups()
servers.shift
big_array = Array.new
servers.each { |server|

	begin
		url= "http://#{server}:4569/status"
		file = open(url)
		status = file.read
	rescue
		#had an error reaching the server. Just move on.
		next
	end
	
	begin
		url= "http://#{server}:4569/metrics"
		file = open(url)
		metrics = file.read
	rescue
		#had an error reachting the server. Just move one.
		next
	end

	begin
		url= "http://#{server}:4569/happy"
		file = open(url)
		happy = file.read
	rescue
		#had an error reachting the server. Just move one.
		next
	end
	
	big_array << {"status" => status,"happy" => happy,"metrics" => metrics,"TimeStamp" => Time.new.strftime("%Y-%m-%d %H:%M"),"Server" => server}

}

couch_server = CouchRest.database("http://<hostname>:5984/watchcat_stats")
big_array.each { |server_metrics|

	#submit the doc to the couch
	response = couch_server.save_doc(server_metrics)
}

