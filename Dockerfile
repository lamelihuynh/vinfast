FROM php:8.2-apache

# PostgreSQL (pdo_pgsql) + MongoDB (pecl mongodb) + composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libssl-dev unzip git \
    && docker-php-ext-install pdo_pgsql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && printf 'output_buffering=4096\nupload_max_filesize=8M\npost_max_size=10M\ndate.timezone=Asia/Ho_Chi_Minh\n' > /usr/local/etc/php/conf.d/app.ini \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cài vendor/ trước để cache layer khi chỉ sửa code
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

COPY . /var/www/html/

RUN mkdir -p public/images/uploads public/images/avatars public/images/news \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 public/images/uploads public/images/avatars public/images/news

EXPOSE 80

CMD ["apache2-foreground"]
