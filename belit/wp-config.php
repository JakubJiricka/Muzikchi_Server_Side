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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'i2443985_wp9');

/** MySQL database username */
define('DB_USER', 'i2443985_wp9');

/** MySQL database password */
define('DB_PASSWORD', 'O.r0p7TSJsguek4f4vu30');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'ZbyRGqQQqhQzB0rWKuGRxDOhT2skvs3yr06i2c89ldAcCEpA5RPP8o42JTKlRCFV');
define('SECURE_AUTH_KEY',  'pqTvLaBRu0GFUIlS2LpVCNy4CYZaosY8Fn3vA1qPhikjPc4VKmEc0hscdmYnHJNl');
define('LOGGED_IN_KEY',    'sZ18YHZ1Y1RktR1u42iud9pL3JtpyKI3g9huItZWot7AhgjeeYccqgOyyOVhNuuU');
define('NONCE_KEY',        'IcXHSLESChT4kN8k2ozeQRS7AYshacAhA7y4OUINyxmNJDzYEpvB8Ao7mGAz4Tx8');
define('AUTH_SALT',        'l3m8pScBYaPuQUo0uz57Y9fntZn0kkXur4gkqVAVmcT2qWj6iuHviEcL3YkLpf3X');
define('SECURE_AUTH_SALT', 'xVMNNvgdUm6eJFvYm3rSiSIxa4icbiq4KTRxSX8iKRcTEvZXsGN08rDxk2AFMUvy');
define('LOGGED_IN_SALT',   '5YMphQMF9m0po8QOJNYfr14m77R9414wxAEFN0dRL9Uo30Cu7JKPSogK0nvnanD0');
define('NONCE_SALT',       'CsDCngCYgEZesyz0DuN31Mc9EqLmyZItdriRzqL6JFjWyTQnLftGS8XiMMU54zhI');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define('WP_MEMORY_LIMIT', '512M');
