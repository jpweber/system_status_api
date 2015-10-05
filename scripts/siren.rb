#!/usr/bin/env/ruby

require 'open-uri'
require 'json'
require 'net/smtp'
require 'couchrest'
require_relative 'AlertLogging'
require_relative 'Apns'

couch_server = CouchRest.database("http://<hostname>:5984/watchcat_hosts")
results = couch_server.view('wc_hosts/active_hostnames')['rows']

servers = Array.new
results.each do | result |
  servers << result["value"]
end

puts servers

#instanciate the Alert Logging class for saving and updating alert records
alert = AlertLogging.new

#cache last seen timestamp for use throught each run
last_seen = Time.now.to_i

def send_mail(subject, body)
    
  message = <<MESSAGE_END
From: Watchcat <watchcat@yourdomain.net>
To: Devs <devs@yourdomain.com>
Subject: #{subject}


#{body}
        
MESSAGE_END
        
    Net::SMTP.start('<mail sever>') do |smtp|
        smtp.send_message message, 'watchcat@yourdomain.net', 'devs@yourdomain.com'
    end		
          
end

# get device UUIDs
def get_devices(alert)
	query = "SELECT uuid FROM devices;"
	uuids = alert.execute_query(query)
	if (uuids)
		return uuids
	else
		return false
	end
	
end

#build message for new alert email notification
def send_new_notifcation(server, metric, metric_details)
  servername = server.upcase
  #puts metric_details.class
  subject = "[WATCHCAT] Issue on #{server}"
  body = "#{servername} has a problem with #{metric[server]}. \n #{metric[server]} is \n"
  if (metric_details.class == Array)
    metric_details.each do |detail|
      detail.each do |k,v|
        body << "#{k} - #{v} \n" 
      end
      body <<  "---------- \n"
    end
  else
    body << metric_details.to_s
  end
  
  #send email
  send_mail(subject, body)
  
end

def send_push_notification(server, metric, uuids)
	puts "in push notification method"
	# instantiate apns class
	apns = APNS.new
	
	# create push message
	message = "#{server} has a problem with #{metric[server]}"
	
	uuids.each { |uuid|
		puts "Sending push to #{uuid['uuid']}"
		results = apns.send_notification(uuid['uuid'], message)
	}

	# close the connection to Apple push servers	
	apns.close

end

#build message for alert cleared notification
def send_cleared_notification(server, metric)
  subject = "[WATCHCAT] Issue CLEARED on #{server}"
  body = "#{server} #{metric} problem has cleared."
  
  #send email
  send_mail(subject,body)
end

#connect to API end point
def api_connect(server, endpoint)
  url= "http://#{server}:4569/#{endpoint}"
  #puts url
  begin 
    response = open(url).read
  rescue Errno::ECONNREFUSED
    return :unreachabe
  rescue Errno::ENETUNREACH
    return :unreachabe
  rescue Errno::ETIMEDOUT
    return :unreachabe
  rescue Errno::EHOSTUNREACH
    return :unreachable
  end
  
  return response
end

def happy_check(servers, alert, last_seen)
    unhappy_servers = Array.new
    servers.each { |server|
      happy = api_connect(server.to_sym, :happy)

      if (happy == :unreachabe)
          metric = Hash.new
          metric = {server => "Reachability"}
          metric_details = "Not Reachable. Server or API is down"
          #create alert signature
          signature = alert.createsig(server+metric[server])
          #first check if this alert already exists
          alert_id = alert.alert_exists(signature)
          #if alert exists update the last seen timestamp
          if (alert_id)
            alert.update_alert(alert_id, last_seen)
          else
            #else save new alert record
            alert.save_alert(signature, server, metric, metric_details, last_seen)
            #send email alert with the server, specific metric that has a problem, and the values of that metric
            send_new_notifcation(server, metric, metric_details)
             
            uuids = get_devices(alert)
            send_push_notification(server, metric, uuids)
          end

          next
      end
      
      if(happy == "false")
        happy = false
      end
    
      if(happy == false)
        unhappy_servers << server
      end
    }
       unhappy_servers
    if unhappy_servers.count > 0
        return unhappy_servers
    else
        return nil
    end
end

def status_check(server)
  unhappy_statuses = Array.new
  status = api_connect(server, :status)
  if !status
    unhappy_statuses << {server => "Server Unreachable"}
    return unhappy_statuses
  end
  if status.include?("false")
    unhappy_statuses << {server => JSON.parse(status).key(false)}
  end
 
  return unhappy_statuses
end

def get_metric_details(server, metric)
  #gets all metric details
  metric_values = api_connect(server, :metrics)
  metric_values = JSON.parse(metric_values)
  
  #get just the details of the down metrics
  metric_alert_details = metric_values[metric[server]]
  return metric_alert_details
  
end

unhappy_servers = happy_check(servers, alert, last_seen)
puts unhappy_servers


#if nothing is in the unhappy_servers array we're done
if unhappy_servers.nil?
    puts "Everything's shiny captain!"
else
  #but if we have something keep going to find out the actual issue to report
    puts "Observed unhealthy status, digging deeper"
    unhappy_servers.each { |server|
      #get the status of individual metrics
      unhappy_metrics = status_check(server)
    
      #get the details of all down metrics
      metric_details = "" 
      unhappy_metrics.each { |metric|
       #puts metric
        
       metric_details = get_metric_details(server, metric)
       #puts metric_details
       
       #create alert signature
       signature = alert.createsig(server+metric[server])
       
       #first check if this alert already exists
       alert_id = alert.alert_exists(signature)
       
       #if alert exists update the last seen timestamp
       if (alert_id)
         alert.update_alert(alert_id, last_seen)
       else
         #else save new alert record
         alert.save_alert(signature, server, metric, metric_details, last_seen)
         #send email alert with the server, specific metric that has a problem, and the values of that metric
         send_new_notifcation(server, metric, metric_details)
         
         uuids = get_devices(alert)
         send_push_notification(server, metric, uuids)
         
       end
       
      }

    }

end

#now clear any alerts that are no longer present
expired_alerts = alert.get_expired_alerts(last_seen)
#clear alerts and send alert cleared notification
if (expired_alerts)
  expired_alerts.each { |expired_alert|
    id = expired_alert['id']
    server = expired_alert['server']
    metric = expired_alert['metric']

    alert.clear_alert(id)
    send_cleared_notification(server, metric)
  }
end


