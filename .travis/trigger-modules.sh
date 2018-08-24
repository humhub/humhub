#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# early exit when pull request || when not master
if [ "${TRAVIS_PULL_REQUEST}" != "false" ] || [ "${TRAVIS_BRANCH}" != "master" ]; then
    exit 0;
fi

# trigger build for calendar
curl -s -X POST \
    https://api.travis-ci.org/repo/humhub%2Fhumhub-modules-calendar/requests \
    -H 'Accept: application/json' \
    -H 'Authorization: token '${AUTH_TOKEN} \
    -H 'Content-Type: application/json' \
    -H 'Travis-API-Version: 3' \
    -d '{"request":{"branch":"master","config":{"merge_mode":"deep_merge","env":{"matrix":["HUMHUB_VERSION=master"]}}}}'