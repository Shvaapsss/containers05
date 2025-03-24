<?php
// ** MySQL settings ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', 'your_database_name' );

/** Имя пользователя MySQL */
define( 'DB_USER', 'your_database_user' );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', 'your_database_password' );

/** Имя хоста MySQL */
define( 'DB_HOST', 'localhost' );

/** Кодировка базы данных */
define( 'DB_CHARSET', 'utf8' );

/** Сетевой порядок сортировки базы данных */
define( 'DB_COLLATE', '' );

/**#@+
 * Ключи аутентификации и соли.
 *
 * Замените эти значения на уникальные фразы!
 * Можно сгенерировать их с помощью https://api.wordpress.org/secret-key/1.1/salt/
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

/**
 * Префикс таблиц базы данных.
 *
 * В случае использования нескольких сайтов на одной базе данных, можно изменить префикс.
 * Пример: 'wp1_' или 'wp2_'. Будьте осторожны при изменении этого значения.
 */
$table_prefix  = 'wp_';

/**
 * Для разработки. Установите значение true, чтобы выводить уведомления об ошибках.
 */
define( 'WP_DEBUG', false );

/* Это все, что нужно для настройки WordPress. Прочитайте документацию для получения дальнейших настроек */

/** Путь к файлу WordPress */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

/** Настроить переменные WordPress и подключиться к файлам */
require_once(ABSPATH . 'wp-settings.php');
