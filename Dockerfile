FROM php:7.4-alpine

RUN apk update && apk add logrotate dcron run-parts && rm -rf /var/cache/apk/*
RUN mkdir -p /var/log/cron && mkdir -m 0644 -p /var/spool/cron/crontabs && touch /var/log/cron/cron.log && mkdir -m 0644 -p /etc/cron.d

ENTRYPOINT ["scripts/docker-entry.sh"]
CMD ["scripts/docker-cmd.sh"]
