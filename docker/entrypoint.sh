#!/bin/sh
set -e

# Render (y la mayoría de PaaS) asignan el puerto a usar vía la variable de
# entorno PORT; Apache por defecto solo escucha el 80, así que lo ajustamos
# al arrancar el contenedor en vez de dejarlo fijo en la imagen.
PORT="${PORT:-80}"

sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

exec "$@"
