#!/bin/bash

# Lints the OpenAPI sources in src/ and renders them into index.html, the whole API on one
# self-contained page: `redocly lint` checks every endpoint document against the rule set in
# redocly.yaml (strict: warnings fail the build), then `redocly join` merges every source into
# one document (src/index.yaml first, supplying the info block), rendered with a sidebar
# across all modules. `src/common.yaml` holds shared components only — every document $refs
# it, nothing reads it on its own. The page is rendered into template.hbs, the renderer's own
# page plus the stylesheet that puts the reference's name beneath the sidebar logo.
#
# index.html is committed, so the reference opens straight from a checkout (no server needed)
# without a build step. Run this after changing a source and commit the result.
#
# The page loads only from its own origin: `--disableGoogleFont` keeps webfonts out, the logo
# is a local file, and the two assets the renderer would have the page pull from Redocly's
# CDN are vendored next to it and referenced relatively:
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

# Pinned: the rendered pages are committed, so every run has to produce the same output. An
# upcoming Redocly CLI release moves build-docs to Redoc 3 - bump deliberately, re-render
# everything and commit the result in one go.
REDOCLY_CLI="@redocly/cli@2.54.2"

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

# Lints every endpoint document before anything is rendered, so an invalid schema or an
# operation without an `operationId` (what client generators key on) fails the build instead
# of being rendered into a page that looks fine. The rule set comes from redocly.yaml
# (`recommended-strict`: every warning is an error, the sources are kept clean, not merely
# valid), and `set -e` turns a non-zero exit into a failed build. `src/common.yaml` is
# components-only and is validated through the $refs of the documents embedding it; linted on
# its own it would only report the `servers` and `paths` it has no use for.
lint_sources() {
    local sources
    sources="$(find src -name '*.yaml' ! -name 'common.yaml' ! -name '.*' | sort)"

    echo "--------- lint ---------"
    # shellcheck disable=SC2086 # word splitting intended, no spaces in source names
    npx "$REDOCLY_CLI" lint $sources
}

# Renders index.html: every source joined into one document (`redocly join`; src/index.yaml
# goes first and supplies the info block), so one page carries the whole API with a sidebar
# across all modules and a search over all of them. Each tag's description comes from the
# source defining it; `--without-x-tag-groups` keeps the sidebar a flat list of modules
# rather than one group per source file. The joined document is a build intermediate.
render_index() {
    local joined="src/.index.joined.yaml"
    local sources
    sources="$(find src -name '*.yaml' ! -name 'index.yaml' ! -name 'common.yaml' ! -name '.*' | sort)"

    echo "--------- index.html (all documents) ---------"
    # shellcheck disable=SC2086 # word splitting intended, no spaces in source names
    npx "$REDOCLY_CLI" join src/index.yaml $sources -o "$joined" --without-x-tag-groups
    # `join` carries info and servers over from the first file but drops the global `security`
    # requirement, which would leave every operation without its "Authorizations" block and
    # the page without an Authentication section. Re-attach the block from src/index.yaml.
    if ! grep -q '^security:' "$joined"; then
        awk '/^security:/ { p = 1 } p && /^[^ ]/ && !/^security:/ { p = 0 } p' src/index.yaml >> "$joined"
    fi
    # template.hbs is the renderer's default page with one stylesheet added (the sidebar
    # logo's caption); the Redoc script tag `localize` rewrites still comes from `redocHead`.
    npx "$REDOCLY_CLI" build-docs "$joined" -o index.html --disableGoogleFont --template template.hbs
    rm -f "$joined"
    localize "index.html"
}

lint_sources
render_index
