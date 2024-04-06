FROM nginx:1.25

WORKDIR /workspace

ENV TZ=UTC

COPY ./infra/docker/nginx/*.conf /etc/nginx/conf.d/

# ssl_certificate
COPY ./infra/docker/nginx/ssl/certs/* /etc/ssl/certs/
