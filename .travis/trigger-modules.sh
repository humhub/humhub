#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

curl -s -X POST \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -H "Travis-API-Version: 3" \
    -H "Authorization: token ${AUTH_TOKEN}" \
    -d "{\"request\": {\"branch\": \"master\"}" \
    https://api.travis-ci.com/repo/humhub%2Fhumhub-modules-calendar/requests