#!/bin/bash

set -e

trap "exit" SIGINT SIGTERM

# TODO: Check if this is needed for first startup
# it turns out that this line right here is the reason why it took AGES for the mediawiki
# container to start up, and removing it made it BLAZINGLY fast (please don't kill me).
# But I suspect it's very much needed for first startup, so I have to check that ig

# chown -R $WWW_USER:$WWW_GROUP $MW_VOLUME $MW_HOME

cd $MW_HOME

# Make sure we're not confused by old, incompletely-shutdown httpd
# context after restarting the container.  httpd won't start correctly
# if it thinks it is already running.

cron

############### Run Apache ###############
rm -rf /run/apache2/*

exec apachectl -e info -D FOREGROUND
