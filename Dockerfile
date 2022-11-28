FROM php:7.4-apache

RUN apt-get update && \
    apt-get -y install tzdata cron libcurl4-openssl-dev python3-pip
	
RUN pip3 install PyP100

RUN docker-php-ext-install curl sockets

RUN cp /usr/share/zoneinfo/Europe/Vienna /etc/localtime && \
    echo "Europe/Vienna" > /etc/timezone

#RUN apt-get -y remove tzdata
RUN rm -rf /var/cache/apk/*

# Copy cron file to the cron.d directory
COPY cron /etc/cron.d/cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cron

# Apply cron job
RUN crontab /etc/cron.d/cron

# Create the log file to be able to run tail
RUN mkdir -p /var/log/cron

COPY devices.ini /etc/

RUN rm -rf /var/www/html/*

COPY www /var/www/html

# Add a command to base-image entrypont scritp
RUN sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground