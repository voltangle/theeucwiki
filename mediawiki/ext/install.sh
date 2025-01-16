#!/bin/bash

# set -x;
set -e;

cd $MW_HOME/extensions

if [ $1 = 'download' ]; then
    # Download all extensions
    LIST=$(cat /ext/extensions.csv | grep ',');
    for i in $LIST; do
        EXT=$(sed 's/,[a-z]*//g' <<< $i)
        if [ $(echo $i | grep -o ',' | wc -l) -lt 2 ]; then
            git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/extensions/$EXT
        else
            BRANCH=$(cut -d ',' -f 3 <<< $i)
            git clone --depth 1 -b $BRANCH https://gerrit.wikimedia.org/r/mediawiki/extensions/$EXT
        fi
    done
fi

if [ $1 = 'install' ]; then
    LIST=$(cat /ext/extensions.csv | grep 'composer' | sed 's/,[a-z]*//g');
    # Run composer install for every extension that needs it
    for i in $LIST; do
        cd $i
        composer install --no-dev
        cd ..
    done
fi

# TODO: implement custom actions for select extensions
