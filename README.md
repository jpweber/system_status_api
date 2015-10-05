# Watch Cat System Monitoring api

# This is not preseted so much as for others to use but as reference for other. But feel free to use it if you want. 

## Connecting
host = server IP
port = 4569

## API End Points
* /happy
If your host has a hostname, a load average below 2 and has been up for more than 10 minutes then you’ll get the JSON string back reflecting whether the box is happy or not

* /status
Will return a JSON representation of the boolean state of the tests

* /metrics
examine the actual values.


repo contains a webui and api


### Startup and Shutdown
sudo service watchcat start | stop | status | restart
 

####Deployment Script:
The deploy script can be used to update all instances of watchcat on remote servers. It mostly works but it does have some kinks to still be worked out.
It lives in the deployment dir and is called wc_deploy.rb.

This takes a list of hosts and runs remote ssh commands on the specific server to call the watchcat_update.sh script. Which is just like the install shell script but trimmed down to only handle the update parts.

All servers that get watchcat installed get a watchcat user and this is how the remote commands are run for the deployment script. 

Currently it is not tied to git pushes or anything via webhooks, but it easily could be. Right now its still being manually run on the tools server /var/www/tools/deploy

### Extensions
> need to add how to add an extension here and a descripton of how the system works.

### Where things are located
#### API
config file is in /etc/watchcat
app source code is in /usr/src/watchcat
init script is in /etc/init.d

#### Config

two views:
http://coubhdbserver:5984/watchcat_hosts/_design/wc_hosts/_view/by_hostname 
returns all info with hostname as key

http://couchdbserver:5984/watchcat_hosts/_design/wc_hosts/_view/hostnames
returns just the hostname

#### Required gems
* sinatra
* daemons
* parseconfig
* sys-filesystem
* usagewatch
* couchrest

```
sudo gem install sinatra
sudo gem install daemons
sudo gem install parseconfig
sudo gem install sys-filesystem
sudo gem install usagewatch
sudo gem install couchrest
```


### WebUI
all webui resources are in webui/
web view requires couchlib

### Stats Gethering script
stat_gather.rb - script meant to be run as cron job that hits the API endpoints and writes the results in to a database for trending / historic analysis  
#### Required Gems
* couchrest
* parseconfig

```
sudo gem install couchrest  
sudo gem install paseconfig
```