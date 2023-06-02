ARG SWOOLE_DOCKER_VERSION

FROM phpswoole/swoole:${SWOOLE_DOCKER_VERSION}

RUN set -eux \
    && docker-php-ext-install -j$(nproc) bcmath
