ARG SWOOLE_DOCKER_VERSION

FROM phpswoole/swoole:${SWOOLE_DOCKER_VERSION}

RUN set -eux \
    && apt-get update && apt-get -y install libzip-dev \
    && docker-php-ext-install -j$(nproc) bcmath zip
