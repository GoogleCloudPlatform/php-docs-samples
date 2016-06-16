<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// Register GCS stream wrapper
set_include_path(__DIR__ . '/../vendor/google/appengine-php-sdk');
require_once(__DIR__ . '/../vendor/autoload.php');
stream_wrapper_register(
    'gs',
    '\google\appengine\ext\cloud_storage_streams\CloudStorageStreamWrapper',
    0);

// Bucket name for the media upload
define('GOOGLE_CLOUD_STORAGE_BUCKET', '{{project_id}}.appspot.com');

// $onGae is true on production.
$onGae = filter_var(getenv('GAE_VM'), FILTER_VALIDATE_BOOLEAN);

// Cache settings
define('WP_CACHE', $onGae);
$batcache = [
    'seconds' => 0,
    'max_age' => 30 * 60, // 30 minutes
    'debug' => false
];
if ($onGae) {
    $memcached_servers = array(
        'default' => array(
            getenv('MEMCACHE_PORT_11211_TCP_ADDR')
            . ':' . getenv('MEMCACHE_PORT_11211_TCP_PORT')
        )
    );
}

// Disable pseudo cron behavior
define('DISABLE_WP_CRON', true);

// Determine HTTP or HTTPS, then set WP_SITEURL and WP_HOME
if (isset($_SERVER['HTTP_HOST'])) {
    define('HTTP_HOST', $_SERVER['HTTP_HOST']);
} else {
    define('HTTP_HOST', 'localhost');
}
// Use https on MVMs.
define('WP_HOME', $onGae ? 'https://' . HTTP_HOST : 'http://' . HTTP_HOST);
define('WP_SITEURL', $onGae ? 'https://' . HTTP_HOST : 'http://' . HTTP_HOST);

// Force SSL for admin pages
define('FORCE_SSL_ADMIN', $onGae);

// Get HTTPS value from the App Engine specific header.
$_SERVER['HTTPS'] = $onGae ? $_SERVER['HTTP_X_APPENGINE_HTTPS'] : false;

// ** MySQL settings - You can get this info from your web host ** //
if ($onGae) {
    /** Production environment */
    define('DB_HOST', ':/cloudsql/{{db_connection}}');
    /** The name of the database for WordPress */
    define('DB_NAME', '{{db_name}}');
    /** MySQL database username */
    define('DB_USER', '{{db_user}}');
    /** MySQL database password */
    define('DB_PASSWORD', '{{db_password}}');
} else {
    /** Local environment */
    define('DB_HOST', '127.0.0.1');
    /** The name of the database for WordPress */
    define('DB_NAME', '{{db_name}}');
    /** MySQL database username */
    define('DB_USER', '{{local_db_user}}');
    /** MySQL database password */
    define('DB_PASSWORD', '{{local_db_password}}');
}

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define('AUTH_KEY',         '{{auth_key}}');
define('SECURE_AUTH_KEY',  '{{secure_auth_key}}');
define('LOGGED_IN_KEY',    '{{logged_in_key}}');
define('NONCE_KEY',        '{{nonce_key}}');
define('AUTH_SALT',        '{{auth_salt}}');
define('SECURE_AUTH_SALT', '{{secure_auth_salt}}');
define('LOGGED_IN_SALT',   '{{logged_in_salt}}');
define('NONCE_SALT',       '{{nonce_salt}}');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', !$onGae);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
