#!/bin/sh

. $(dirname $0)/.env
echo "$NEST_USERNAME"
echo "$NEST_PASSWORD"
php $(dirname $0)/src/update_nest_target_humidity.php > /var/log/nest.log
