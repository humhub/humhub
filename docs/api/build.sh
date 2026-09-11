#!/bin/bash

# Renders the OpenAPI sources in src/ into this directory, one self-contained HTML page per
# document. `src/common.yaml` holds shared components only and is skipped — every document
# $refs it, nothing reads it on its own.
#
# The rendered pages are committed, so an installation ships its API reference (reachable at
# /docs/api/) without a build step. Run this after changing a source and commit the result.
#
# A rendered page loads only from its own origin: `--disableGoogleFont` keeps webfonts out,
# and the two assets the renderer would have the page pull from Redocly's CDN are vendored
# next to the pages and referenced relatively:
#
#   redoc.standalone.js    the Redoc bundle itself — without it a page stays empty
#   redoc-logo-mini.svg    the "API docs by Redocly" badge the bundle requests at runtime
#
# plus redoc.standalone.js.LICENSE.txt, the license notice the bundle's own banner points at.
#
# An installation's own documentation has to work without internet access (or behind a CSP
# that only allows its own origin) and must not send its readers to a third party. Commit
# both files along with the pages.
#
# The vendored bundle is (re)downloaded whenever the integrity hash the renderer emits stops
# matching the one recorded in redoc.standalone.js.sha384, so it follows the renderer's Redoc
# version by itself. It is verified against that hash BEFORE the single logo URL inside it is
# rewritten to the vendored SVG, which is why the recorded hash is the upstream one and not
# the hash of the file in this directory.
#
# Usage: docs/api/build.sh [document]      # e.g. `docs/api/build.sh comment`

set -e

cd "$(dirname "$0")" || exit 1

BUNDLE="redoc.standalone.js"
BUNDLE_HASH="$BUNDLE.sha384"
LOGO="redoc-logo-mini.svg"
LOGO_URL="https://cdn.redoc.ly/redoc/logo-mini.svg"

# `sha384-<base64>` of the pristine download — the shape the renderer writes into the CDN
# script tag's integrity attribute, so the two can be compared directly.
file_integrity() {
    printf 'sha384-%s' "$(openssl dgst -sha384 -binary "$1" | openssl base64 -A)"
}

vendor_bundle() {
    local url="$1"
    local integrity="$2"

    if [ -f "$BUNDLE" ] && [ -f "$BUNDLE_HASH" ] && [ "$(cat "$BUNDLE_HASH")" = "$integrity" ]; then
        return 0
    fi

    echo "--------- vendoring $url ---------"
    curl -fsSL "$url" -o "$BUNDLE.upstream"

    if [ "$(file_integrity "$BUNDLE.upstream")" != "$integrity" ]; then
        rm -f "$BUNDLE.upstream"
        echo "Integrity mismatch for $url — expected $integrity" >&2
        exit 1
    fi

    if ! grep -q "$LOGO_URL" "$BUNDLE.upstream"; then
        rm -f "$BUNDLE.upstream"
        echo "Redoc no longer requests $LOGO_URL — adjust this script for the new asset" >&2
        exit 1
    fi

    curl -fsSL "$LOGO_URL" -o "$LOGO"
    curl -fsSL "$url.LICENSE.txt" -o "$BUNDLE.LICENSE.txt"

    sed "s|$LOGO_URL|$LOGO|" "$BUNDLE.upstream" > "$BUNDLE"
    printf '%s' "$integrity" > "$BUNDLE_HASH"
    rm -f "$BUNDLE.upstream"
}

# Points the page at the vendored bundle instead of the CDN the renderer emits.
localize() {
    local page="$1"
    local tag
    local url
    local integrity

    tag="$(grep -o '<script src="https://cdn\.redocly\.com[^>]*></script>' "$page" || true)"

    if [ -z "$tag" ]; then
        echo "No Redoc script tag found in $page" >&2
        exit 1
    fi

    url="$(printf '%s' "$tag" | sed -n 's/.*src="\([^"]*\)".*/\1/p')"
    integrity="$(printf '%s' "$tag" | sed -n 's/.*integrity="\([^"]*\)".*/\1/p')"

    vendor_bundle "$url" "$integrity"

    # Not `sed -i`: its in-place syntax differs between GNU and BSD sed.
    sed "s|<script src=\"https://cdn\.redocly\.com[^>]*></script>|<script src=\"$BUNDLE\"></script>|" \
        "$page" > "$page.tmp"
    mv "$page.tmp" "$page"
}

render() {
    local source="$1"
    local name
    name="$(basename "$source" .yaml)"

    [ "$name" = "common" ] && return 0

    echo "--------- $source ---------------------"
    npx @redocly/cli build-docs "$source" -o "$name.html" --disableGoogleFont
    localize "$name.html"
}

if [ -n "$1" ]; then
    render "src/$1.yaml"
    exit 0
fi

for source in src/*.yaml; do
    render "$source"
done
