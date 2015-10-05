#!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 13:11:51
# @Last Modified by:   James Weber
# @Last Modified time: 2014-11-03 13:12:27

# For monitoring if the CDR Attempt worker is running

def attempt_worker(function)
  if File.exists?('/var/run/attempt_worker.pid')
      worker_pid = IO.read('/var/run/attempt_worker.pid').chomp.to_i
      worker_processes = Hash.new 
    begin
      Process.getpgid(worker_pid)
        worker_processes = {"attempt_worker" => true}
    rescue Errno::ESRCH
        worker_processes = {"attempt_worker" => false}
     end
   else
     worker_processes = {"attempt_worker" => false}
  end
  
  case function
  when :status
    return false if worker_processes.has_value?(false)
    return true
  when :metric
    return worker_processes
  end
end