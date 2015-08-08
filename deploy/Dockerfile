FROM maestrano/docker-lamp:stable
MAINTAINER Maestrano <it@maestrano.com>

RUN apt-get -y update && apt-get -y upgrade 

# Add ansible configuration
ADD ansible /etc/ansible
WORKDIR /etc/ansible

# Install TimeTrex
RUN ansible-playbook -vvv -i hosts site.yml

# Map TimeTrex mutable volumes
VOLUME ["/etc/mysql", "/var/lib/mysql", "/etc/apache2", "/etc/php5", "/var/log/apache2", "/var/log/mysql", "/var/lib/timetrex"]
