
# default versions to test against
# these can be overridden by setting the environment variables in the shell
PHP_VERSION=php-5.6.8
YII_VERSION=dev-master

# ensure all the configuration variables above are in environment of the shell commands below
export

help:
	@echo "make test    - run phpunit tests using a docker environment"
#	@echo "make clean   - stop docker and remove container"

test: docker-php
	composer require "yiisoft/yii2:${YII_VERSION}" --prefer-dist
	composer install --prefer-dist
	docker run --rm=true -v $(shell pwd):/opt/test yiitest/php:${PHP_VERSION} phpunit --verbose --color

docker-php: dockerfiles
	cd tests/docker/php && sh build.sh

dockerfiles:
	test -d tests/docker || git clone https://github.com/cebe/jenkins-test-docker tests/docker
	cd tests/docker && git checkout -- . && git pull
	mkdir -p tests/dockerids

