# Используем образ Debian
FROM debian:latest

# Устанавливаем Apache, PHP, модуль PHP для Apache, php-mysql и MariaDB
RUN apt-get update && \
    apt-get install -y apache2 php libapache2-mod-php php-mysql mariadb-server && \
    apt-get clean

# Монтируем тома для хранения данных MariaDB и логов
VOLUME /var/lib/mysql
VOLUME /var/log

# Устанавливаем Supervisor для управления процессами
RUN apt-get install -y supervisor

# Загружаем и распаковываем WordPress
ADD https://wordpress.org/latest.tar.gz /var/www/html/

# Копируем конфигурационные файлы Apache2
COPY files/apache2/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY files/apache2/apache2.conf /etc/apache2/apache2.conf

# Копируем конфигурацию PHP
COPY files/php/php.ini /etc/php/8.2/apache2/php.ini

# Копируем конфигурацию MariaDB
COPY files/mariadb/50-server.cnf /etc/mysql/mariadb.conf.d/50-server.cnf

# Копируем конфигурацию Supervisor
COPY files/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Создаем директорию для сокета MariaDB
RUN mkdir /var/run/mysqld && chown mysql:mysql /var/run/mysqld

# Открываем порт для HTTP
EXPOSE 80

# Запускаем Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
