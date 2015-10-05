#!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 13:15:32
# @Last Modified by:   James Weber
# @Last Modified time: 2014-11-03 13:17:27


# To determine what process should be checked
# Then calling the methods only for those specific processes

def processes(function)
  if $config.processes
    checked_process = $config.processes.split(", ")
    puts checked_process 
  end

  process_info = Array.new 
  checked_process.each do |process_metric|
    process_result = send(process_metric.to_sym, function)
    process_info << process_result
  end
    
  case function
  when :status
    return true unless process_info.include? false
    return false
  when :metric
    return process_info.flatten
  end
  
end