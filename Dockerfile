FROM php:7.4-alpine

RUN apk update && apk add logrotate dcron run-parts && rm -rf /var/cache/apk/*
RUN mkdir -p /var/log/cron && mkdir -m 0644 -p /var/spool/cron/crontabs && touch /var/log/cron/cron.log && mkdir -m 0644 -p /etc/cron.d

COPY hello-cron /etc/cron.d/hello-cron
RUN chmod 0644 /etc/cron.d/hello-cron

# Apply cron job
RUN crontab /etc/cron.d/hello-cron

# Create the log file to be able to run tail
# RUN touch /var/log/cron.log

CMD ["cron", "-f"]

# https://stackoverflow.com/questions/37458287/how-to-run-a-cron-job-inside-a-docker-container
