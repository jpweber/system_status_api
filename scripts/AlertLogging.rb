#!/usr/bin/env/ruby
require 'mysql2'

class AlertLogging
  
  #create alert signature
  def createsig(body)    
    hash = Digest::MD5.hexdigest(body)
    return hash
  end
  
  #create db connection
  def db_connection
      client = Mysql2::Client.new(
        :host => '<hostname>', 
        :port => 3306, 
        :database => 'watchcat',
        :username => 'watchcat', 
        :password => 'password')
      
        return client
  end
  
  def execute_query(query)  
    #debug
    #p query     
    begin
      results = self.db_connection.query(query)    
    rescue Mysql2::Error => e
      puts e.errno
      puts e.error
      return false        
    end
    
    return results
    
  end
  

  #check if alert exists
  def alert_exists(hash)
    query = "SELECT id FROM alerts WHERE HASH = '#{hash}' AND cleared = 0" 
    results = self.execute_query(query)
    if (results.count > 0)
      results.each { |result|
        return result['id']
      }
    else
      return false
    end
    
  end

  #save initial alert info to database
  def save_alert(hash, server, metric, metric_details, last_seen)
        
    servername = server.upcase    
    description = "#{servername} has a problem with #{metric[server]}"
    
    #format metric details for use as the body of the logged alert
    body = " "
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
    
    #create query    
    query = "INSERT INTO watchcat.alerts (`id`, `hash`, `server`, `metric`,`description`, `body`, `created`, `last_seen`, `cleared`) VALUES('', '#{hash}', '#{servername}', '#{metric[server]}', '#{description}', '#{body}', UNIX_TIMESTAMP(), '#{last_seen}', 0)"
    
    #execute the query
    self.execute_query(query)
   
  end
  
  #update alert record  
  def update_alert(id, last_seen)
    query = "UPDATE watchcat.alerts SET `last_seen` = #{last_seen}, `updated` = UNIX_TIMESTAMP() WHERE `id` = #{id} "
    results = self.execute_query(query)
        
  end
  
  def get_expired_alerts(last_seen)
    query = "SELECT id, `server`, `metric`, `description` FROM watchcat.alerts WHERE last_seen != #{last_seen} AND cleared = 0"
    #p query
    results = self.execute_query(query)
    if (results.count > 0)
      expired_alerts = []
      
      results.each { |result|
        expired_alerts << result
      }
      
      return expired_alerts
    end
    #if we didn't get an rows returned back return false
    return false
  end
  
  #set alert to cleared    
  def clear_alert(id)  
    query = "UPDATE watchcat.alerts SET `updated` = UNIX_TIMESTAMP(), `cleared` = 1 WHERE `id` = #{id}"
    results = self.execute_query(query)
    return results
  end


end
