FROM nginx:1.25 AS base

WORKDIR /workspace

ENV TZ=UTC

# 開発環境
FROM base AS development
COPY ./infra/docker/nginx/development.conf /etc/nginx/conf.d/
COPY ./infra/docker/nginx/ssl/certs/* /etc/ssl/certs/

# 本番1号機
FROM base AS production1
COPY ./infra/docker/nginx/production.conf /etc/nginx/conf.d/
# COPY /etc/letsencrypt/live/*/* /etc/ssl/certs/

# 本番2号機
FROM base AS production2

# COPY /etc/letsencrypt/live/tk2-409-45999.vs.sakura.ne.jp/* /etc/ssl/certs/
