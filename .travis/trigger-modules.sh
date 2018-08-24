#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# change to build directory
cd ${TRAVIS_BUILD_DIR}

# build only when branch master and no pull request
if [ ${TRAVIS_BRANCH} = "master" ] && [ ${TRAVIS_PULL_REQUEST} = "false" ]; then

    # trigger build for calendar
    curl -X POST \
        https://api.travis-ci.org/repo/humhub%2Fhumhub-modules-calendar/requests \
        -H 'Accept: application/json' \
        -H 'Authorization: token '${AUTH_TOKEN} \
        -H 'Content-Type: application/json' \
        -H 'Travis-API-Version: 3' \
        -d '{"request":{"branch":"master","config":{"env":{"global":["HUMHUB_PATH=/opt/humhub"],"matrix":["HUMHUB_VERSION=master"]}}}}'

fi