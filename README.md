# Лабораторная работа №5: Запуск сайта в контейнере

## Цель работы

Выполнив данную работу, студент сможет подготовить образ контейнера для запуска веб-сайта на базе Apache HTTP Server + PHP (mod_php) + MariaDB.

## Задание

Создать Dockerfile для сборки образа контейнера, который будет содержать веб-сайт на базе Apache HTTP Server + PHP (mod_php) + MariaDB. База данных MariaDB должна храниться в монтируемом томе. Сервер должен быть доступен по порту 8000.

Установить сайт WordPress. Проверить работоспособность сайта.

## Подготовка

Для выполнения данной работы необходимо иметь установленный на компьютере Docker.

Для выполнения работы необходимо иметь опыт выполнения лабораторной работы №3.

## Выполнение

### 1. Создание репозитория и подготовка файлов

Создайте репозиторий **containers05** и скопируйте его себе на компьютер.

Создайте в папке **containers05** папку **files**, а также подпапки:

- **files/apache2** — для файлов конфигурации apache2;
- **files/php** — для файлов конфигурации php;
- **files/mariadb** — для файлов конфигурации mariadb.

### 2. Dockerfile

Создайте в папке **containers05** файл **Dockerfile** со следующим содержимым:
```
create from debian image
FROM debian:latest

install apache2, php, mod_php for apache2, php-mysql and mariadb
RUN apt-get update &&
apt-get install -y apache2 php libapache2-mod-php php-mysql mariadb-server &&
apt-get clean
```

Постройте образ контейнера с именем **apache2-php-mariadb**.

### 3. Запуск контейнера

Создайте контейнер **apache2-php-mariadb** из образа **apache2-php-mariadb** и запустите его в фоновом режиме с командой запуска bash.

Скопируйте из контейнера файлы конфигурации apache2, php, mariadb в папку **files/** на компьютере. Для этого, в контексте проекта, выполните команды:

```
docker cp apache2-php-mariadb:/etc/apache2/sites-available/000-default.conf files/apache2/ docker cp apache2-php-mariadb:/etc/apache2/apache2.conf files/apache2/ docker cp apache2-php-mariadb:/etc/php/8.2/apache2/php.ini files/php/ docker cp apache2-php-mariadb:/etc/mysql/mariadb.conf.d/50-server.cnf files/mariadb/
```

После выполнения команд в папке **files/** должны появиться файлы конфигурации apache2, php и mariadb. Проверьте их наличие. Остановите и удалите контейнер **apache2-php-mariadb**.

### 4. Настройка конфигурационных файлов

#### Конфигурационный файл apache2

Откройте файл **files/apache2/000-default.conf**, найдите строку **#ServerName www.example.com** и замените её на **ServerName localhost**.

Найдите строку **ServerAdmin webmaster@localhost** и замените в ней почтовый адрес на свой.

После строки **DocumentRoot /var/www/html** добавьте следующие строки:

```
DirectoryIndex index.php index.html
```

Сохраните файл и закройте.

В конце файла **files/apache2/apache2.conf** добавьте следующую строку:
```
ServerName localhost
```

#### Конфигурационный файл php

Откройте файл **files/php/php.ini**, найдите строку **;error_log = php_errors.log** и замените её на **error_log = /var/log/php_errors.log**.

Настройте параметры **memory_limit**, **upload_max_filesize**, **post_max_size** и **max_execution_time** следующим образом:

```
memory_limit = 128M upload_max_filesize = 128M post_max_size = 128M max_execution_time = 120
```

Сохраните файл и закройте.

#### Конфигурационный файл mariadb

Откройте файл **files/mariadb/50-server.cnf**, найдите строку **#log_error = /var/log/mysql/error.log** и раскомментируйте её.

Сохраните файл и закройте.

### 5. Создание скрипта запуска

Создайте в папке **files** папку **supervisor** и файл **supervisord.conf** со следующим содержимым:

```
[supervisord] nodaemon=true logfile=/dev/null user=root

apache2
[program:apache2] command=/usr/sbin/apache2ctl -D FOREGROUND autostart=true autorestart=true startretries=3 stderr_logfile=/proc/self/fd/2 user=root

mariadb
[program:mariadb] command=/usr/sbin/mariadbd --user=mysql autostart=true autorestart=true startretries=3 stderr_logfile=/proc/self/fd/2 user=mysql
```


### 6. Дополнение Dockerfile

Откройте файл **Dockerfile** и добавьте в него следующие строки:

После инструкции **FROM** добавьте монтирование томов:

```
mount volume for mysql data
VOLUME /var/lib/mysql

mount volume for logs
VOLUME /var/log
```

В инструкции **RUN** добавьте установку пакета **supervisor**.

После инструкции **RUN** добавьте копирование и распаковку сайта WordPress:

```
add wordpress files to /var/www/html
ADD https://wordpress.org/latest.tar.gz /var/www/html/
```

После копирования файлов WordPress добавьте копирование конфигурационных файлов apache2, php, mariadb, а также скрипта запуска:
```
copy the configuration file for apache2 from files/ directory
COPY files/apache2/000-default.conf /etc/apache2/sites-available/000-default.conf COPY files/apache2/apache2.conf /etc/apache2/apache2.conf

copy the configuration file for php from files/ directory
COPY files/php/php.ini /etc/php/8.2/apache2/php.ini

copy the configuration file for mysql from files/ directory
COPY files/mariadb/50-server.cnf /etc/mysql/mariadb.conf.d/50-server.cnf

copy the supervisor configuration file
COPY files/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
```
Для функционирования mariadb создайте папку **/var/run/mysqld** и установите права на неё:

```
create mysql socket directory
RUN mkdir /var/run/mysqld && chown mysql:mysql /var/run/mysqld
```

Откройте порт 80:

``` 
EXPOSE 80
```

Добавьте команду запуска **supervisord**:
```
start supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 7. Сборка и запуск контейнера

Соберите образ контейнера с именем **apache2-php-mariadb** и запустите контейнер **apache2-php-mariadb** из образа **apache2-php-mariadb**.

Проверьте наличие сайта WordPress в папке **/var/www/html/**. Проверьте изменения конфигурационного файла apache2.

### 8. Создание базы данных и пользователя

Создайте базу данных **wordpress** и пользователя **wordpress** с паролем **wordpress** в контейнере **apache2-php-mariadb**. Для этого, в контейнере **apache2-php-mariadb**, выполните команды:
```
mysql CREATE DATABASE wordpress; CREATE USER 'wordpress'@'localhost' IDENTIFIED BY 'wordpress'; GRANT ALL PRIVILEGES ON wordpress.* TO 'wordpress'@'localhost'; FLUSH PRIVILEGES; EXIT;
```

### 9. Создание файла конфигурации WordPress

Откройте в браузере сайт WordPress по адресу **http://localhost/**. Укажите параметры подключения к базе данных:

- имя базы данных: **wordpress**;
- имя пользователя: **wordpress**;
- пароль: **wordpress**;
- адрес сервера базы данных: **localhost**;
- префикс таблиц: **wp_**.

Скопируйте содержимое файла конфигурации в файл **files/wp-config.php** на компьютере.
![image](https://github.com/user-attachments/assets/cf291a94-8013-4a5e-be10-003846cd08cf)


### 10. Добавление файла конфигурации WordPress в Dockerfile

Добавьте в файл **Dockerfile** следующие строки:
```
copy the configuration file for wordpress from files/ directory
COPY files/wp-config.php /var/www/html/wordpress/wp-config.php
```

### 11. Пересборка образа и запуск

Пересоберите образ контейнера с именем **apache2-php-mariadb** и запустите контейнер **apache2-php-mariadb** из образа **apache2-php-mariadb**. Проверьте работоспособность сайта WordPress.

## Ответы на вопросы

1. **Какие файлы конфигурации были изменены?**

   Изменены следующие файлы конфигурации:

   - **000-default.conf** (Apache)
   - **apache2.conf** (Apache)
   - **php.ini** (PHP)
   - **50-server.cnf** (MariaDB)

2. **За что отвечает инструкция DirectoryIndex в файле конфигурации apache2?**

   Инструкция **DirectoryIndex** указывает, какие файлы Apache будет искать и показывать при обращении к директории, если не указан конкретный файл. В данном случае, при обращении к корню сайта, Apache будет искать файл **index.php** или **index.html**.

3. **Зачем нужен файл wp-config.php?**

   Файл **wp-config.php** необходим для настройки параметров подключения к базе данных, таких как имя базы данных, имя пользователя и пароль. Он также содержит параметры безопасности и настройки сайта WordPress.

4. **За что отвечает параметр post_max_size в файле конфигурации php?**

   Параметр **post_max_size** в файле конфигурации PHP ограничивает максимальный размер данных, которые могут быть отправлены через HTTP POST-запрос. Это важно для контроля размера загружаемых файлов.

5. **Укажите, на ваш взгляд, какие недостатки есть в созданном образе контейнера?**

   Одним из возможных недостатков является отсутствие оптимизации для продакшн-среды, например, настройка безопасности или использование менее ресурсоемких решений для базы данных и веб-сервера.

## Выводы

В ходе выполнения лабораторной работы был создан образ контейнера с веб-сайтом на базе Apache, PHP и MariaDB. Настроены необходимые конфигурационные файлы и проведены тесты на работоспособность сайта WordPress. В процессе работы были изучены основы работы с Docker и конфигурацией контейнеров для веб-приложений.


![image](https://github.com/user-attachments/assets/5a893ab1-f112-4e17-86e7-5b5282fdffc9)


![image](https://github.com/user-attachments/assets/660875b2-1158-41b4-a28c-6192523045c8)


![image](https://github.com/user-attachments/assets/7c15d505-8e73-46bc-8d7d-41b965e44496)

