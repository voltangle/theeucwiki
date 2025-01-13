#!/bin/sh
# Taken from https://www.mediawiki.org/wiki/MediaWiki-Docker/Extension/CirrusSearch#Modify_LocalSettings.php
# This script has to be run once to initialize the ElasticSearch search index. Please run
# from the docker compose directory with `task init-elastic`.

# Configure the search index and populate it with content
cd /var/www/html/w/
php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks --indexOnSkip
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipParse
php maintenance/runJobs.php
php extensions/CirrusSearch/maintenance/UpdateSuggesterIndex.php
