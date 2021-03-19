<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://ru.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', 'u1284666_elementary' );

/** Имя пользователя MySQL */
define( 'DB_USER', 'u1284666_default' );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', 's6o_zCRl' );

/** Имя сервера MySQL */
define( 'DB_HOST', 'localhost' );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'A0;z?_Ihb+~Wn&ZI=:^eKlrUgHsE2gY+h<RQczl1zu7)-Mo{}j]3,Q-c.}MJ2KD2' );
define( 'SECURE_AUTH_KEY',  'le:KTOm-22=ECzF&wCh>OC$SLqnruxj/f|p *y&>7Up]0Wd38I@R1?EO!aZ]lGJ_' );
define( 'LOGGED_IN_KEY',    '=b|__QjbRKdar)xcw8H=:i<KFBmjL4a&`<9_u[^>:P2g}u5yadGZ[lMBXEMm^Q<-' );
define( 'NONCE_KEY',        'o^LV_C? O_3>QpU+.}% Cb5T-%af*29H{s Qv:I>X69`;X4HXBpXOAO}xK3H#1V ' );
define( 'AUTH_SALT',        'Xb+LQ]Y28*n2}#R0pgf#n*U|GoaE72u6D>dh+XvoHX(.B^D|<FbR_`3y)gJ@De~q' );
define( 'SECURE_AUTH_SALT', '3c[ZdQHg^RA(%?A~&0tH $oNSjAStOuNI&UNn&57OeE?WoyPIsRe[u%&:F?,nWN(' );
define( 'LOGGED_IN_SALT',   '$h<DqFvpxi?nX_%thS tGxdt)#/#P$|)KgsRKrB0+qjSGTCFciu+d3cY;oKKa;zR' );
define( 'NONCE_SALT',       'V0pUM$C{<%lguK^#BoZ_0TD:M|8m>)y0w-hKfd?#oq4i#RN-c7d~y}<|LBG)_=?`' );

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в документации.
 *
 * @link https://ru.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once ABSPATH . 'wp-settings.php';
