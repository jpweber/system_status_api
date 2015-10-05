#!/bin/bash
#Startup script for watchcat api daemon

/usr/bin/ruby /usr/src/watchcat/watchcat_controller.rb $1
RETVAL=$?

exit $RETVAL
