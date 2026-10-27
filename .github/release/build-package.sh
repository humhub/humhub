#!/usr/bin/env bash
#
# Builds the HumHub release packages for a tag.
#
# Usage: build-package.sh <tag> <source dir> <dist dir>
#
# <source dir> is a checkout of <tag>. Creates in <dist dir>:
#   humhub-<version>.zip, humhub-<version>.tar.gz, humhub-<version>.json
#
# See docs/develop/internals-release-package.md

set -euo pipefail

if [ $# -ne 3 ]; then
    echo "Usage: $0 <tag> <source dir> <dist dir>" >&2
    exit 1
fi

TAG="$1"
SRC="$(realpath "$2")"
DIST="$(realpath -m "$3")"
TOOLS="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Same as ReleaseHelper::cleanupVersionTag() of humhub.com: v1.19.2 -> 1.19.2, v1.20.0-rc.1 -> 1.20.0-rc1
VERSION="$(printf '%s' "$TAG" | sed -e 's/v\.//g' -e 's/v//g' -e 's/ //g' -e 's/rc\./rc/g')"
NAME="humhub-$VERSION"

# Must match the version format accepted by the humhub.com upload API
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+(-(beta\.[0-9]+|rc[0-9]+))?$ ]]; then
    echo "Invalid version \"$VERSION\" derived from tag \"$TAG\"" >&2
    exit 1
fi

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
mkdir -p "$DIST"

echo "::group::Composer (with dev, for asset build)"
cd "$SRC"
composer install --prefer-dist --no-interaction --no-progress
echo "::endgroup::"

if [ -f package.json ]; then
    echo "::group::Build assets"
    npm ci
    npx grunt build-assets
    echo "::endgroup::"
fi

# Since 1.19 built assets are generated and no longer committed
if [ ! -d static ] && [ -d protected/humhub/resources ] && [ ! -f protected/humhub/config/assets-prod.php ]; then
    echo "Asset build failed: protected/humhub/config/assets-prod.php was not generated!" >&2
    exit 1
fi

echo "::group::Composer (no dev)"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
echo "::endgroup::"

echo "::group::Pack $NAME"
rsync -a --exclude-from="$TOOLS/exclude.txt" "$SRC/" "$WORK/$NAME/"
cd "$WORK"
rm -f "$DIST/$NAME.zip" "$DIST/$NAME.tar.gz"
zip -9 -q -r "$DIST/$NAME.zip" "$NAME"
tar czf "$DIST/$NAME.tar.gz" "$NAME"
echo "::endgroup::"

file_info() {
    jq -n \
        --arg name "$(basename "$1")" \
        --argjson size "$(stat -c %s "$1")" \
        --arg sha256 "$(sha256sum "$1" | cut -d' ' -f1)" \
        '{name: $name, size: $size, sha256: $sha256}'
}

jq -n \
    --arg version "$VERSION" \
    --arg tag "$TAG" \
    --arg commit "$(git -C "$SRC" rev-parse HEAD)" \
    --arg phpVersion "$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')" \
    --arg buildDate "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    --argjson zip "$(file_info "$DIST/$NAME.zip")" \
    --argjson targz "$(file_info "$DIST/$NAME.tar.gz")" \
    '{version: $version, tag: $tag, commit: $commit, phpVersion: $phpVersion, buildDate: $buildDate, files: {zip: $zip, targz: $targz}}' \
    > "$DIST/$NAME.json"

echo "Built $NAME:"
ls -l "$DIST"
