#!/bin/sh

set -e

export NGINX_UPSTREAM="${NGINX_UPSTREAM:-unix:/run/php-fpm.sock}"
export NGINX_CLIENT_MAX_BODY_SIZE="${NGINX_CLIENT_MAX_BODY_SIZE:-10m}"
export NGINX_KEEPALIVE_TIMEOUT="${NGINX_KEEPALIVE_TIMEOUT:-65}"

# shellcheck disable=SC2046
defined_envs=$(printf "\${%s} " $(env | grep -E "^NGINX_.*" | cut -d= -f1))
envsubst "$defined_envs" </etc/nginx/nginx.conf >/tmp/nginx.conf
cat /tmp/nginx.conf >/etc/nginx/nginx.conf
rm /tmp/nginx.conf

exit 0
