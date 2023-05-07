#!/bin/sh

# Reverseproxy
HUMHUB_REVERSEPROXY_WHITELIST=${HUMHUB_REVERSEPROXY_WHITELIST:-"127.0.0.1"}

echo "Setting HUMHUB_REVERSEPROXY_WHITELIST"
reverseproxyips=$(echo "$HUMHUB_REVERSEPROXY_WHITELIST" | tr ";" "\n")
touch /etc/nginx/allowedips.conf
for ip in $reverseproxyips
do
	echo "allow $ip;" >>  /etc/nginx/allowedips.conf
	echo "Added $ip to Reverseproxy Whitelist"
done
