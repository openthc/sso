#
# OpenTHC SSO Docker Image
#

FROM openthc/web:latest

#
# Environment
ENV OPENTHC_SERVER_NAME="sso.docker.openthc.org"

#
# Install
# RUN git clone https://github.com/openthc/sso.git /opt/openthc/sso \
COPY ./ /opt/openthc/sso
RUN cd /opt/openthc/sso \
	&& /opt/openthc/sso/make.sh

#
# Configure
COPY ./docker/apache2.conf  /etc/apache2/sites-enabled/000-default.conf
COPY ./docker/xdebug.ini    /etc/php/7.4/mods-available/xdebug.ini
COPY ./docker/init.sh       /init.sh

#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]

#
# Ports
EXPOSE 80

#
# Go!
ENTRYPOINT [ "/init.sh" ]
