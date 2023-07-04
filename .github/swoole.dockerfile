ARG SWOOLE_DOCKER_VERSION

FROM phpswoole/swoole:${SWOOLE_DOCKER_VERSION}

RUN set -eux \
    && apt-get update && apt-get -y install libzip-dev zlib1g-dev libjpeg-dev libpng-dev libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev \
	&& docker-php-ext-configure gd \
    --with-webp \
    --with-jpeg \
    --with-xpm \
    --with-freetype \
    && docker-php-ext-install -j$(nproc) bcmath zip gd
