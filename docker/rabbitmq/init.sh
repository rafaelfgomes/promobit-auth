#!/bin/sh

rabbitmqctl add_user 'promobit' 'passwd'
rabbitmqctl set_user_tags 'promobit' administrator
rabbitmqctl set_permissions -p / 'promobit' ".*" ".*" ".*"

