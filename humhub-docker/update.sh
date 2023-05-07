#!/bin/bash

set -xeo pipefail

UPDATE_NEEDED=false
CUR_VERSION=""
NEW_VERSION=""
GIT_BRANCH=""

upstream_versions=$(curl -s https://api.github.com/repos/humhub/humhub/releases | jq -r '.[] | select(.prerelease==false) | .name' | sort -n)
local_versions=$(cat versions.txt) # to avoid problems when writing doing loop
while IFS= read -r line; do
    local_version_prefix=$(echo "$line" | cut -d' ' -f2)
    local_version=$(echo "$line" | cut -d' ' -f1)
    latest_upstream_version=$(echo "$upstream_versions" | grep "$local_version_prefix" | tail -n1)
    if [ "$local_version" != "$latest_upstream_version" ]; then
        echo "$local_version_prefix: UPDATE NEEDED! ($local_version != $latest_upstream_version)"
        postfix=${line/$local_version $local_version_prefix/}
        sed -i "s/$line/$latest_upstream_version $local_version_prefix$postfix/" versions.txt
        CUR_VERSION="$local_version"
        NEW_VERSION="$latest_upstream_version"
        UPDATE_NEEDED=true
        export CUR_VERSION
        export NEW_VERSION
        export UPDATE_NEEDED
        break
    else
        echo "$local_version_prefix: no update needed ($local_version == $latest_upstream_version)"
    fi
done <<< "$local_versions"

if [ $UPDATE_NEEDED ]; then
    GIT_BRANCH="update-$NEW_VERSION"
    export GIT_BRANCH

    git branch "$GIT_BRANCH" || true
    git checkout "$GIT_BRANCH"

    git add versions.txt
    git commit -m "update from $CUR_VERSION to $NEW_VERSION"
fi
