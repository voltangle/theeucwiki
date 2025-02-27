#!/bin/bash

set -x;
set -e;

cd $MW_HOME/extensions

NON_WMGERRIT_EXTS=(
    "https://github.com/edwardspec/mediawiki-moderation.git"
    "https://github.com/StarCitizenTools/mediawiki-extensions-ShortDescription.git"
    "https://github.com/StarCitizenTools/mediawiki-extensions-TabberNeue.git"
    "https://github.com/Universal-Omega/DynamicPageList3.git"
    "https://github.com/Universal-Omega/DiscordNotifications.git"
)

NON_WMGERRIT_EXTS_NAMES=(
    "Moderation"
    "ShortDescription"
    "TabberNeue"
    "DynamicPageList3"
    "DiscordNotifications"
)

NON_WMGERRIT_EXTS_BRANCHES=(
    "master"
    "main"
    "main"
    "REL1_43"
    "REL1_40"
)

if [ $1 = 'download' ]; then
    # Download all extensions
    LIST=$(cat /ext/extensions.csv | grep ',');
    for i in $LIST; do
        EXT=$(sed 's/,[a-z0-9.]*//g' <<< $i)
        echo $EXT
        if [ $(echo $i | grep -o ',' | wc -l) -lt 2 ]; then
            git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/extensions/$EXT
        else
            BRANCH=$(cut -d ',' -f 3 <<< $i)
            git clone --depth 1 -b $BRANCH https://gerrit.wikimedia.org/r/mediawiki/extensions/$EXT
        fi
    done
    LENGTH=${#NON_WMGERRIT_EXTS[@]}
    LENGTH=$(($LENGTH-1))
    for i in $(seq 0 $LENGTH); do
        git clone --depth 1 -b ${NON_WMGERRIT_EXTS_BRANCHES[$i]} \
            ${NON_WMGERRIT_EXTS[$i]} \
            ${NON_WMGERRIT_EXTS_NAMES[$i]}
    done
fi

if [ $1 = 'install' ]; then
    LIST=$(cat /ext/extensions.csv | grep -v 'simple');
    # Run composer install for every extension that needs it
    for i in $LIST; do
        EXT=$(sed 's/,[a-z0-9.]*//g' <<< $i)
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

    cd $MW_HOME
    COMPOSER=composer.local.json composer require --no-update professional-wiki/network:~2.0
    composer update professional-wiki/network --no-dev -o
fi

