#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# change to build directory
cd ${TRAVIS_BUILD_DIR}

curl -X POST \
  https://api.travis-ci.org/repo/humhub%2Fhumhub-modules-calendar/requests \
  -H 'Accept: application/json' \
  -H 'Authorization: token '${AUTH_TOKEN} \
  -H 'Content-Type: application/json' \
  -H 'Travis-API-Version: 3' \
  -d '{"request": {"branch": "master"}}'