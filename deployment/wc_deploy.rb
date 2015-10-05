#!/usr/bin/env ruby
#
# Deployment script for watchcat
require 'couchrest'
require 'optparse'
require 'net/ssh'
require 'net/scp'


options = {}
opt_parser = OptionParser.new do |opt|
  opt.banner = "Usage: ./wc_deploy.rb [OPTIONS]"
  opt.separator  ""
  opt.separator  "Options"

  opt.on("-n","--hostname HOSTNAME","The hostname of the server you wish to deploy to") do |hostname|
    options[:hostname] = hostname
  end

  opt.on("-v","--verbose","Run with verbose output, debug mode.") do
    options[:verbose] = true
  end

  opt.on("-l","--list","List the hosts we could deploy to.") do
    options[:list] = true
  end

  opt.on("-a","--all","Deploy to all hosts currently using watchcat") do
    options[:all_deploy] = true
  end

  opt.on("-h","--help","help") do
    puts opt_parser
    exit
  end
end

opt_parser.parse!
puts options

# convert verbose to global
$verbose = options[:verbose]

# wrapper method for outputting information if verbose is enabled.
def verbose(string)
  if $verbose
    puts string
  end
end

#test scp files over to server
def deploy(hosts)
  thread_list = [] #keep track of our threads
  hosts.each do | h | 
    thread_list  << Thread.new {  #add a new thread to
      verbose("deplying to #{h}")
      #get filenames in api dir
      Dir.chdir("../api/")
      files =  Dir.glob("*.rb")
      files.each do | file |
        verbose("Uploading File #{file}")
        Net::SSH.start(h, 'watchcat') do |ssh|
          output = ssh.scp.upload!(file, "/usr/src/watchcat/")
        end
      end

      #and now do the extensions dir
      Dir.chdir("../api/extensions/")
      files =  Dir.glob("*.rb")
      verbose("Uploading Extensions")
      files.each do | file |
        verbose("Uploading File #{file}")
        Net::SSH.start(h, 'watchcat') do |ssh|
          output = ssh.scp.upload!(file, "/usr/src/watchcat/extensions")
        end
      end

    }
  end
  thread_list.each {|t| t.join} #wait for each thread to complete  
end

def get_hosts()
  wc_hosts = Array.new
  verbose("Getting list of hostnames")
  # connect to couch to get the hosts metrics
  couch_server = CouchRest.database("http://<hostname>/watchcat_hosts")
  # get the metrics for this host
  hosts = couch_server.view('wc_hosts/hostnames')['rows']

  # hosts that we don't want to try an update. standbys are off and mysql02 is purposely held back
  excluded_hosts = ["hostname"]

  # puts hosts
  #unwrap just the hostname portion from the results
  hosts.each do |host|
    unless excluded_hosts.include?host['key']
      wc_hosts << host['key']
    end
  end
  return wc_hosts
end

if options[:list]
  puts "List of hosts we could deploy to:"
  puts get_hosts
  exit
end

# only build a list of hostnames if the all deploy options was passed
if options[:all_deploy]
  wc_hosts = get_hosts
end

# init array with the single host if a single host was passed
if options[:hostname]
  wc_hosts = Array.new()
  wc_hosts << options[:hostname]
end

# run the deployment
if defined?(wc_hosts)
  deploy(wc_hosts)
end
