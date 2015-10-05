#!/bin/sh

#download the file
wget http://<hostname>/watchcat-current.tar.gz


#untar it
tar -zxvf watchcat-current.tar.gz

##
##TODO
##

#chek for required gems

#install required gems if needed


#
#copy files in to system dirs
#

#make the watchcat user
useradd watchcat -d /home/watchcat -m

#config file
mkdir /etc/watchcat
cp watchcat/server_mon.conf /etc/watchcat

#application and app controller
mkdir /usr/src/watchcat
cp -R watchcat/api/* /usr/src/watchcat/
chmod +x /usr/src/watchcat/watchcat_controller.rb


#chown ownership to watchcat for all the stuff
#in /usr/src/watchcat
chown -R watchcat /usr/src/watchcat
chgrp -R devs /usr/src/watchcat
chmod -R g+w devs /usr/src/watchcat

#init script
cp watchcat/watchcat_init.sh /etc/init.d/watchcat
chmod +x /etc/init.d/watchcat

#copy backend scripts that will run as cron jobs
cp -R watchcat/scripts /usr/src/watchcat/

#add watchcat user ability to sudo for the service command
echo "Run the following commands to allow the watchcat user restart the watchcat service"
echo "#watchcat restart >> /etc/sudoers"
echo "watchcat        ALL = NOPASSWD: /usr/sbin/service watchcat * >> /etc/sudoers"

echo "If you have not already install the Bluetone version of usagewatch go ahead and install the gem in the watchcat/custom_gems dir"

#remove the tar file
rm watchcat-current.tar.gz