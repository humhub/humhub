#!/bin/bash

set -eo pipefail

src_image_base="ghcr.io/mriedmann/humhub"
dst_image="${1:-docker.io/mriedmann/humhub}"
variants=("allinone" "nginx" "phponly")

function publish_image() {
    src_version="$1"

    for variant in "${variants[@]}"; do
        if [ "$variant" == "allinone" ]; then
            postfix=""
        else
            postfix="-$variant"
        fi

        src_image="$src_image_base-$variant"
        src_tag="${src_version}"
        for version in "$@"; do
            dst_tag="${version}$postfix"    
            echo "copy $src_image:$src_tag => $dst_image:$dst_tag"
            skopeo copy "docker://$src_image:$src_tag" "docker://$dst_image:$dst_tag"
        done
    done
}

while read -r line; do 
    IFS=' ' read -ra versions <<< "$line"
    publish_image "${versions[0]}" "${versions[@]:1}"
done < versions.txt
 