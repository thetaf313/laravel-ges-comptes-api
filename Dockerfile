FROM richarvey/nginx-php-fpm:latest

COPY . .

# Copier la configuration Supervisor
COPY conf/supervisor-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Installer les dépendances PHP
RUN composer install --optimize-autoloader --no-dev

# Copier la configuration de production
COPY .env.production .env

# Générer la documentation Swagger
RUN php artisan l5-swagger:generate

# Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Rendre les scripts exécutables
RUN chmod +x start.sh script/start-with-worker.sh

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

CMD ["/start.sh"]