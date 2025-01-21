# syntax=docker/dockerfile:1-labs

FROM ubuntu:24.10
ARG DEBIAN_FRONTEND=noninteractive
SHELL ["/bin/bash", "-c"]

# Install requered packages
RUN set -x; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        apache2 \
        libapache2-mod-php \
        php \
        php-mysql \
        php-cli \
        php-gd \
        php-curl \
        php-mbstring \
        php-xml \
        php-apcu \
        php-intl \
        php-zip \
        php-memcached \
        php-pear \
	    python3 \
	    python3-pip \
        python3-pygments \
        imagemagick \
        netcat-traditional \
        git \
	    curl \
        unzip \
        mysql-client \
        wget \
        patch \
    && pear install mail net_smtp \
    # TODO: this shit stinks
    && pip3 install pygments --break-system-packages \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/apt/archives/* \
    && a2enmod rewrite \
    && rm /var/www/html/index.html \
    && rm -rf /etc/apache2/sites-enabled/*

# install composer
RUN set -x; \
    curl -sS https://getcomposer.org/installer -o composer-setup.php \ 
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer --version

# When upgrading MediaWiki, change this version number
# WARNING: If you change MW_HOME or MW_VOLUME, please also update them in docker-compose.yml
ENV MW_VERSION=REL1_43 \
    MW_HOME=/var/www/html/w \
    MW_VOLUME=/mediawiki \
    WWW_USER=www-data \
    WWW_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2

ENV CITIZEN_VERSION=v2.40.2
ENV TEMPLATESTYLESEXTENDER_VERSION=v1.2.0

# logs should go to stdout / stderr
RUN set -ex \
    && ln -sfT /dev/stderr "$APACHE_LOG_DIR/error.log" \
    && ln -sfT /dev/stdout "$APACHE_LOG_DIR/access.log" \
    && ln -sfT /dev/stdout "$APACHE_LOG_DIR/other_vhosts_access.log"

##### MediaWiki Core download
RUN set -x; \
    mkdir -p $MW_HOME \
    && git clone \
        --depth 1 \
        -b $MW_VERSION \
        https://gerrit.wikimedia.org/r/mediawiki/core.git \
        $MW_HOME

##### Bundled skins, see https://www.mediawiki.org/wiki/Bundled_extensions
RUN set -x; \
    cd $MW_HOME/skins \
    && git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/skins/Vector \
    && git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/skins/MinervaNeue \
    && git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/skins/Timeless \
    && git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/skins/MonoBook \
    && git clone --depth 1 -b $MW_VERSION https://gerrit.wikimedia.org/r/mediawiki/skins/CologneBlue

##### Citizen skin
RUN set -x; \
    cd $MW_HOME/skins \
    && git clone --depth 1 -b $CITIZEN_VERSION https://github.com/StarCitizenTools/mediawiki-skins-Citizen.git Citizen

COPY ext/* /ext/

#### Download all extensions
RUN chmod +x /ext/install.sh && /ext/install.sh download
RUN cd $MW_HOME/extensions \
    && git clone --depth 1 \
    https://github.com/StarCitizenTools/mediawiki-extensions-ShortDescription.git \
    ShortDescription

#### Shubara extension
COPY --from=shubara ./ $MW_HOME/extensions/Shubara

COPY patches/ $MW_HOME/patches/

#### Apply pre-install patches
RUN set -x; \
    cd $MW_HOME \
    && chmod +x patches/apply_patches.sh \
    && ./patches/apply_patches.sh pre

#### Install MediaWiki Core composer dependencies
RUN set -x; \
    cd $MW_HOME \
    && composer install --no-dev \
    && chown -R $WWW_USER:$WWW_GROUP images/

#### Install dependencies and set up extensions
RUN /ext/install.sh install

#### Apply post-install patches
RUN set -x; \
    cd $MW_HOME \
    && ./patches/apply_patches.sh post

EXPOSE 80

COPY php.ini /etc/php/7.0/apache2/conf.d/mediawiki.ini

COPY mediawiki.conf /etc/apache2/sites-available/000-mediawiki.conf
RUN set -x; ln -s /etc/apache2/sites-available/000-mediawiki.conf /etc/apache2/sites-enabled/000-mediawiki.conf

COPY run-apache.sh /run-apache.sh
RUN chmod -v +x /run-apache.sh

COPY img/* $MW_HOME/
COPY LocalSettings.php $MW_HOME/LocalSettings.php
COPY systempagelist.txt $MW_HOME/systempagelist.txt
COPY init_elastic.sh /
RUN set -x; chmod +x /init_elastic.sh

CMD ["/run-apache.sh"]

VOLUME ["$MW_HOME/images", "$MW_VOLUME"]

