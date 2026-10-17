# Imagen para desplegar WePago (PHP + Apache) en Render como Web Service tipo Docker.
FROM php:8.3-apache

# Extensiones que usa la app:
#  - curl: llamadas server-side a HelppiuPay (includes/helppiupay.php)
#  - mbstring: validaciones de texto (mb_strlen, mb_substr, etc.)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install curl mbstring \
    && apt-get purge -y --auto-remove libcurl4-openssl-dev libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Evita el warning "Could not reliably determine the server's fully qualified domain name".
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Bloquea el acceso HTTP directo a includes/ (ahí viven las credenciales de
# HelppiuPay), data/ (ahí se escribe el log de pedidos PSE) y backups/. El
# require_once/file_put_contents de la app sigue funcionando porque eso pasa
# por el sistema de archivos, no por Apache.
COPY docker/apache-protect.conf /etc/apache2/conf-enabled/zz-wepago-protect.conf

WORKDIR /var/www/html
COPY . .

# data/pse_orders.log se escribe en tiempo de ejecución: www-data necesita
# permiso de escritura en esa carpeta.
RUN chown -R www-data:www-data /var/www/html/data

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
