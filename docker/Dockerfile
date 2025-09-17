#
# OpenTHC SSO Docker Image
#

FROM openthc/web:latest

#
# Install
COPY ./ /opt/openthc/sso
RUN cd /opt/openthc/sso \
	&& /opt/openthc/sso/make.sh


#
# Copy
COPY ./docker/apache2.conf  /etc/apache2/sites-enabled/000-default.conf
COPY ./docker/init.sh       /init.sh
# COPY ./docker/rootfs/cre-openthc.yaml /opt/openthc/app/etc/cre/openthc.yaml
# COPY ./docker/rootfs/first-run.php    /openthc-config-init.php
# COPY ./docker/rootfs/xdebug.ini /etc/php/7.4/mods-available/xdebug.ini


#
# Ports
EXPOSE 80


#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]


#
# Environment
ENV OPENTHC_SERVER_NAME="sso.docker.openthc.me"


ENTRYPOINT [ "/init.sh" ]
