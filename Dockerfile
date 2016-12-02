FROM debian:jessie

RUN cp /usr/share/zoneinfo/Asia/Yekaterinburg /etc/localtime

RUN apt-get update && apt-get install -y \
    php5-mysql \
    php5-cli \
    libapache2-mod-php5 \
    apache2 \
    wget \
    locate \
    php5-imagick \
    php5-curl \
    php5-xdebug \
    php5-geoip \
    php5-gd \
    php5-intl \
    php5-ldap \
    libgeoip-dev \
    git \
    libxml2-utils

RUN a2enmod rewrite

# geoip
RUN cd /usr/share/GeoIP/ && \
    wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz && \
    gzip -dv GeoLiteCity.dat.gz && \
    mv -v GeoLiteCity.dat GeoIPCity.dat

# composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php && mv composer.phar /usr/local/bin/composer

# для yii2
RUN composer global require "fxp/composer-asset-plugin:~1.1.1"

# токен для гитхаба
RUN composer config -g github-oauth.github.com 66400b93a2d07d70e5009f3359adf9cc244f7c66

# codeception
RUN ln -sf /var/www/html/bin/codecept.sh /usr/local/bin/codecept

# при логине сразу же заходим в директорию с файлами сайта
RUN echo "cd /var/www/html" >> /root/.bashrc

# grunt
RUN apt-get install -y npm
RUN npm install -g grunt-cli
RUN apt-get install nodejs-legacy
# bower
RUN npm install -g bower

CMD /usr/sbin/apache2ctl -D FOREGROUND