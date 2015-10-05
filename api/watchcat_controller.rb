require 'rubygems'
require 'daemons'
require 'syslog'
require 'net/http'
require 'uri'

#https://github.com/derks/ruby-parseconfig
require 'parseconfig.rb'


#pwd  = File.dirname(File.expand_path(__FILE__))
#file = pwd + '/watchcat_api.rb'

Syslog.open("watchcat", Syslog::LOG_PID, Syslog::LOG_DAEMON | Syslog::LOG_LOCAL3)

command = ARGV[0].chomp
case command
  when "run"
    puts "Running In Non Daemon mode"
  when "start"
    puts "Starting WatchCat Loader Daemon"
    Syslog.info("Starting WatchCat Loader Daemon")
  when "stop"
  	puts "Stopping WatchCat Daemon"
  	Syslog.info("Stopping WatchCat Daemon")
  when "restart"
  	puts "Restarting WatchCat Daemon"
  	Syslog.info("Restarting WatchCat Daemon")
  end 
  
config = ParseConfig.new('/etc/watchcat/server_mon.conf')
config_url = config['configfile']['src']
uri = URI(config_url)
#puts uri.inspect
  
def download_config(uri)
	Net::HTTP.start(uri.host) do |http|
	    resp = http.get(uri.path)
	    open("/etc/watchcat/server_mon.conf", "wb") do |file|
	        file.write(resp.body)
	    end
	end
end


#download config file before starting
download_config(uri)

Daemons.run_proc(
  'watchcat', # name of daemon
#  :dir_mode => :normal,
  :dir => "/tmp/" # directory where pid file will be stored
#  :backtrace => true,
#  :monitor => true,
#  :log_output => true
) do
  exec "ruby /usr/src/watchcat/watchcat_api.rb"
end
