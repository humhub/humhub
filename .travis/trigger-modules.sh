#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# only trigger related modules for php 7.2 || early exit when pull request || early exit when not master
if [ "${TRAVIS_PHP_VERSION}" != "7.2" ] || [ "${TRAVIS_PULL_REQUEST}" != "false" ] || [ "${TRAVIS_BRANCH}" != "master" ]; then
    exit 0;
fi

# trigger build for calendar
curl -X POST \
    https://api.travis-ci.org/repo/humhub%2Fhumhub-modules-calendar/requests \
    -H 'Accept: application/json' \
    -H 'Authorization: token '${AUTH_TOKEN} \
    -H 'Content-Type: application/json' \
    -H 'Travis-API-Version: 3' \
    -d '{"request":{"branch":"master","config":{"merge_mode":"deep_merge","env":{"matrix":["HUMHUB_VERSION=master"]}}}}'