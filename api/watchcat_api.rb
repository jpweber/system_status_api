#!/usr/bin/env ruby
#
# HTTP server status daemon
#
require 'rubygems'
require 'sinatra'
require 'json'
require "net/http"
require 'open-uri'
require 'couchrest'
require_relative 'WCHosts'
require_relative 'WCExtensions'

#https://github.com/nethacker/usagewatch
require 'usagewatch'

#https://github.com/derks/ruby-parseconfig
require 'parseconfig.rb'

#https://github.com/djberg96/sys-filesystem
require 'sys/filesystem'
include Sys 


set :bind, '0.0.0.0'
set :port, 4569

# method to find the index of the host we are running on
def get_host_index(host_info, hostname)
    host_info.each_with_index do | host, index | 
        if host.hostname == hostname
            return index 
        end
        
    end
end


$usage_watch = Usagewatch
# get local hostname
$hostname = %x['hostname'].chomp
# connect to couch to get the hosts metrics
couch_server = CouchRest.database("http://<hostname>:5984/watchcat_hosts")

# get the metrics for this host
metrics = couch_server.view('wc_hosts/by_hostname', :key => $hostname)['rows']

$config = nil
metrics.each do | metric |
  $config = WCHosts.new(metric)
end


#define the default metrics that are the same for everyone
registered_metrics = ['config','hostname', 'uptime', 'load_average', 'disk_space', 'mem_usage']

#find any additional metrics in the config file
additional_metrics = $config.metrics.split(", ")

#combine any new metrics in to the register metrics list
registered_metrics = (registered_metrics << additional_metrics).flatten 



helpers do
  def config(function)
    # connect to couch to get the hosts metrics
    couch_server = CouchRest.database("http://<hostname>:5984/watchcat_hosts")

    # get the metrics for this host
    metrics = couch_server.view('wc_hosts/by_hostname', :key => $hostname)['rows']

    $config = nil
    metrics.each do | metric |
      $config = WCHosts.new(metric)
    end
  end

  def hostname(function)
    name = %x['hostname'].chomp

    case function
    when :status
      return false if name == nil
      return true
    when :metric
      return name
    end
  end

  def uptime(function)
    time = IO.read('/proc/uptime').chomp.to_i

    case function
    when :status
      return false if time < 600
      return true
     when :metric
      return time
    end
  end

  def load_average(function)
  	lavg = $usage_watch.uw_load15 
  		
  	case function
    when :status
      return false if lavg > $config.load_threshold.to_f 
      return true
    when :metric
      return lavg
	end
   end
  end

  def disk_space(function)
  	#read from the config file
  	#hard coded for testing
  	disk_space_info = Array.new
    excluded_names = ['none', 'proc', 'nfsd']
    excluded_types = ['fuse.sshfs', 'proc', 'sysfs', 'devpts', 'fusectl', 'tmpfs', 'securityfs', 'rpc_pipefs', 'none', 'cgroup']
  	Filesystem.mounts{ |mount|

      unless excluded_names.include? mount.name
        unless excluded_types.include? mount.mount_type

		  		mount_point = Filesystem.mount_point(mount.mount_point)
			  	fsinfo =  Filesystem.stat(mount_point)
			
			  	free_space = (fsinfo.blocks_free * 4096).to_mb
			  	total_space = (fsinfo.blocks * 4096).to_mb
			  	percent_used = (total_space.to_f - free_space.to_f) / total_space.to_f * 100
			  	  	
			  	disk_space_info << {
				  	'free'         => free_space,
				  	'total'        => total_space, 
				  	'percent_used' => percent_used, 
				  	'mount_point'  => mount_point
			  	}
        end
     end       	
  	}
   	  	
  	case function
    when :status
      disk_space_info.each { |mount| 
	     return false if mount['percent_used'] > 85 
      }
      
      return true
    when :metric
      return disk_space_info
    end
	
  end
   
  def mem_usage(function)
  	mem_percent = $usage_watch.uw_memused
  	  	
  	case function
    when :status
      return false if mem_percent > 95
      return true
    when :metric
      return mem_percent
	end
  
end
  
def build_hash(type, metrics)
  status = Hash.new
  metrics.each do |metric|
    status[metric] = send(metric.to_sym, type)
  end
  return status
end


get '/status' do
  build_hash(:status, registered_metrics).to_json
end

get '/metrics' do
  build_hash(:metric, registered_metrics).to_json
end

get '/happy' do
  return false.to_json if build_hash(:status, registered_metrics).values.include?(false)
  return true.to_json
end