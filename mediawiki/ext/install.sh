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
    LIST=$(cat /ext/extensions.csv | grep -v 'simple');
    # Run composer install for every extension that needs it
    for i in $LIST; do
        EXT=$(sed 's/,[a-z]*//g' <<< $i)
        METHOD=$(cut -d ',' -f 2 <<< $i)
        cd $EXT
        echo Installing $EXT

        if [ $METHOD = 'composer' ]; then
            composer install --no-dev
        fi

        # Additional actions for each extension that needs it
        if [ $EXT = 'VisualEditor' ]; then
            git submodule update --init
        elif [ $EXT = 'Scribunto' ]; then
            chmod a+x includes/Engines/LuaStandalone/binaries/lua5_1_5_linux_64_generic/lua
        elif [ $EXT = 'SyntaxHighlight_GeSHi' ]; then
            chmod a+x pygments/pygmentize
        fi
        cd ..
    done
fi

