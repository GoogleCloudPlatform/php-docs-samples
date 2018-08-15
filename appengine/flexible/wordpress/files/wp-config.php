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

require_once(__DIR__ . '/../vendor/autoload.php');
$storageClient = new Google\Cloud\Storage\StorageClient();
$storageClient->registerStreamWrapper();

// $onGae is true on production.
$onGae = (getenv('GAE_VERSION') !== false);

// Disable pseudo cron behavior
define('DISABLE_WP_CRON', true);

// Determine HTTP or HTTPS, then set WP_SITEURL and WP_HOME
if (isset($_SERVER['HTTP_HOST'])) {
    define('HTTP_HOST', $_SERVER['HTTP_HOST']);
} else {
    define('HTTP_HOST', 'localhost');
}
// Use https on production.
define('WP_HOME', $onGae ? 'https://' . HTTP_HOST : 'http://' . HTTP_HOST);
define('WP_SITEURL', $onGae ? 'https://' . HTTP_HOST : 'http://' . HTTP_HOST);

// Force SSL for admin pages
define('FORCE_SSL_ADMIN', $onGae);

// ** MySQL settings - You can get this info from your web host ** //
if ($onGae) {
    /** Production environment */
    define('DB_HOST', ':/cloudsql/YOUR_DB_CONNECTION');
    /** The name of the database for WordPress */
    define('DB_NAME', 'YOUR_DB_NAME');
    /** MySQL database username */
    define('DB_USER', 'YOUR_DB_USER');
    /** MySQL database password */
    define('DB_PASSWORD', 'YOUR_DB_PASSWORD');
} else {
    /** Local environment */
    define('DB_HOST', '127.0.0.1');
    /** The name of the database for WordPress */
    define('DB_NAME', 'YOUR_DB_NAME');
    /** MySQL database username */
    define('DB_USER', 'YOUR_LOCAL_DB_USER');
    /** MySQL database password */
    define('DB_PASSWORD', 'YOUR_LOCAL_DB_PASSWORD');
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

define('AUTH_KEY',         'YOUR_AUTH_KEY');
define('SECURE_AUTH_KEY',  'YOUR_SECURE_AUTH_KEY');
define('LOGGED_IN_KEY',    'YOUR_LOGGED_IN_KEY');
define('NONCE_KEY',        'YOUR_NONCE_KEY');
define('AUTH_SALT',        'YOUR_AUTH_SALT');
define('SECURE_AUTH_SALT', 'YOUR_SECURE_AUTH_SALT');
define('LOGGED_IN_SALT',   'YOUR_LOGGED_IN_SALT');
define('NONCE_SALT',       'YOUR_NONCE_SALT');

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
