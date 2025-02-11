#!/bin/bash

set -e

# usage bash docker/prod/build.sh

LOCAL=$PWD/"$(dirname "$0")"

cd "${LOCAL}" || exit

function build_app {
  git clone git@github.com:induxx/parsable-file-multi-tool.git project

  cd project
  set -ex
  git checkout develop-imes # should be master one day
  docker-compose down --remove-orphans
  bin/docker/composer install
  rm -rf $( cat ../excluded_files.txt )

  cd -
  mv project app/
}

build_app

cd app

podman build -t induxx/multi-tool:latest .

IMAGE_ID=$(podman images | grep "localhost/induxx/multi-tool" | awk '{print $3}')

# you need VPN for this part
podman push "${IMAGE_ID}" registry.induxx.be:5000/induxx/multi-tool:latest --tls-verify=false

rm -rf project
