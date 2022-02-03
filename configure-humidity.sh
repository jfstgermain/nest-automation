#!/bin/sh

. $(dirname $0)/.env
#echo "$USERNAME"
#echo "$PASSWORD"
echo "$ISSUE_TOKEN"
echo "$COOKIES"
echo "$POSTAL_CODE"
echo "$COUNTRY"

php $(dirname $0)/src/update_nest_target_humidity.php > /var/log/nest.log
