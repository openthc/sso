# syntax=docker/dockerfile:1.19
#
# OpenTHC SSO Docker Image
#

FROM openthc/web:latest

#
# Environment
ENV OPENTHC_SERVICE="sso"
ENV OPENTHC_SERVER_NAME="sso.docker.openthc.org"

#
# Install
# RUN git clone https://github.com/openthc/sso.git /opt/openthc/sso \
COPY --exclude=docker/ ./ /opt/openthc/sso
RUN cd /opt/openthc/sso \
	&& /opt/openthc/sso/make.sh

#
# Configure
COPY ./docker/apache2.conf  /etc/apache2/sites-enabled/000-default.conf
COPY ./docker/Caddyfile     /etc/caddy/Caddyfile
COPY ./docker/xdebug.ini    /etc/php/7.4/mods-available/xdebug.ini
COPY ./docker/init.sh       /init.sh
COPY ./docker/init.php      /opt/openthc/sso/docker/init.php

#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]

#
# Ports
EXPOSE 80

#
# Go!
CMD [ "/init.sh" ]
