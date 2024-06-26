FROM php:8.3-fpm-bullseye AS base

WORKDIR /workspace
EXPOSE 5173

# timezone environment
ENV TZ=UTC \
  # locale
  LANG=en_US.UTF-8 \
  LANGUAGE=en_US:en \
  LC_ALL=en_US.UTF-8 \
  # composer environment
  COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_HOME=/composer

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
  && apt-get -y install --no-install-recommends \
    locales \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    postgresql-client \
    libpq-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && locale-gen en_US.UTF-8 \
  && localedef -f UTF-8 -i en_US en_US.UTF-8 \
  && docker-php-ext-install \
    intl \
    # pdo_mysql \
    pdo_pgsql \
    zip \
    bcmath \
  && composer config -g process-timeout 3600 \
  && composer config -g repos.packagist composer https://packagist.org

#############################################
# node.jsインストール
#############################################
# # パッケージリストを更新し、必要なパッケージをインストール
# RUN apt-get update && \
#     apt-get install -y \
#     ca-certificates \
#     curl \
#     gnupg

# # キーリングのディレクトリを作成
# RUN mkdir -p /etc/apt/keyrings

# # Node.jsのGPGキーをダウンロードしてインストール
# RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

# # Node.jsのメジャーバージョンを設定
# ARG NODE_MAJOR=20

# # Node.jsのリポジトリをsources.listに追加
# RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_MAJOR}.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

# # パッケージリストを再度更新し、Node.jsをインストール
# RUN apt-get update && \
#     apt-get install -y \
#     nodejs

# # イメージサイズを減らすためにクリーンアップ
# RUN apt-get clean && \
#     rm -rf /var/lib/apt/lists/*


#############################################
# 環境別設定
#############################################
FROM base AS development

COPY ./infra/docker/php/php.development.ini /usr/local/etc/php/php.ini

FROM development AS development-xdebug

RUN pecl install xdebug && \
  docker-php-ext-enable xdebug

COPY ./infra/docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

FROM base AS production1

COPY ./infra/docker/php/php.deploy.ini /usr/local/etc/php/php.ini
COPY ./src /workspace

RUN composer install -q -n --no-ansi --no-dev --no-scripts --no-progress --prefer-dist \
  && chmod -R 777 storage bootstrap/cache \
  && php artisan optimize:clear \
  && php artisan optimize


FROM base AS production2

COPY ./infra/docker/php/php.deploy.ini /usr/local/etc/php/php.ini
COPY ./src /workspace

RUN composer install -q -n --no-ansi --no-dev --no-scripts --no-progress --prefer-dist \
  && chmod -R 777 storage bootstrap/cache \
  && php artisan optimize:clear \
  && php artisan optimize
