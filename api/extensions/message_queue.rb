  #!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 13:06:32
# @Last Modified by:   jpweber
# @Last Modified time: 2015-10-05 11:56:20

# For monitoring number of messages in queue

def total_message_queue(function)
  #uri to rabbitmq api
  uri = URI.parse("http://localhost:15672/api/vhosts")
  
  #setup http request
  http = Net::HTTP.new(uri.host, uri.port)
  request = Net::HTTP::Get.new(uri.request_uri)
  #send authentication in headers
  request.basic_auth("guest", "guest")
  response = http.request(request)
  #parse json response to find the total ready count
  parsed = JSON.parse(response.body)
  queue_ready_count = parsed[0]['messages']
  puts parsed[0]['messages']
  $message_queue_stats << { 'total_ready' => queue_ready_count}
end


def message_queue(function)
  $message_queue_stats = Array.new

  #call method for total stats
  total_message_queue(function)

  case function
  when :status
    return false if $message_queue_stats[0]['total_ready'] > $config.message_threshold.to_i
    return true
   when :metric
    return $message_queue_stats
  end
  
end
