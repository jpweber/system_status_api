#!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 13:12:58
# @Last Modified by:   James Weber
# @Last Modified time: 2014-11-03 13:13:24

# For monitoring of the CDR Stop worker is running

def stop_worker(function)
  worker_processes = Hash.new
  if File.exists?('/var/run/stop_worker.pid')
      worker_pid = IO.read('/var/run/stop_worker.pid').chomp.to_i 
    begin
      Process.getpgid(worker_pid)
        worker_processes = {"stop_worker" => true}
    rescue Errno::ESRCH
        worker_processes = {"stop_worker" => false}
     end
   else
     worker_processes = {"stop_worker" => false}
  end
  
  case function
  when :status
    return false if worker_processes.has_value?(false)
    return true
  when :metric
    return worker_processes
  end
end