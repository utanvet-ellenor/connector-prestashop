FROM docker.io/bitnami/prestashop:1.7.8-7

USER 0

RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data && \
    chown -R www-data:www-data /opt/bitnami/

USER www-data:www-data
