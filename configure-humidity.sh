#!/bin/sh
source $(dirname $0)/.env
php $(dirname $0)/src/update_nest_target_humidity.php > /var/log/nest.log
