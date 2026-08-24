#!/bin/bash

# Renders the OpenAPI sources in src/ into this directory, one self-contained HTML page per
# document. `src/common.yaml` holds shared components only and is skipped — every document
# $refs it, nothing reads it on its own.
#
# The rendered pages are committed, so an installation ships its API reference (reachable at
# /docs/api/) without a build step. Run this after changing a source and commit the result.
#
# `--disableGoogleFont` keeps the pages from loading webfonts off Google's CDN — an
# installation's own documentation must not send its readers to a third party. What remains is
# the Redoc bundle itself, which the generated page loads from cdn.redocly.com: a page opened
# without internet access (or behind a CSP that forbids that origin) stays empty. Vendoring
# that bundle instead is an open decision, see docs/develop/concept-api.md.
#
# Usage: docs/api/build.sh [document]      # e.g. `docs/api/build.sh comment`

set -e

cd "$(dirname "$0")" || exit 1

render() {
    local source="$1"
    local name
    name="$(basename "$source" .yaml)"

    [ "$name" = "common" ] && return 0

    echo "--------- $source ---------------------"
    npx @redocly/cli build-docs "$source" -o "$name.html" --disableGoogleFont
}

if [ -n "$1" ]; then
    render "src/$1.yaml"
    exit 0
fi

for source in src/*.yaml; do
    render "$source"
done
