<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'lalasia' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '+}=+_a2N%{9+em+x5}4vXQ1Qp+$?d+2*PDAg1rkuh)+8UbF;9npAyv~#5k6*EqMi' );
define( 'SECURE_AUTH_KEY',  'TuVPHBr;> [~Y(qJ3|f&kq;bQr?-jxsQyb|t~Ot`J;T2oaWIbCW+lFK_uz=c..y7' );
define( 'LOGGED_IN_KEY',    'H`5|JhigrN1H?-C/S5~zinyk8|n((P>_upLENTpJ(g8, bD,lMT4J%zQ q`w@1l0' );
define( 'NONCE_KEY',        '_tlwtycYc6JSr7A/4fEN3CI~>L?O+XP$|moBEh|~~)]g#YcNQDON4$961UJ2b|=7' );
define( 'AUTH_SALT',        '9[w3r^}HfV-,hQcyWuR=6F{6 4?/+[4>k{!32OK,.P*UkKQB_ kHCTjR0f/7|eA~' );
define( 'SECURE_AUTH_SALT', '0Rr}bEozZ(La1+Z4(k.i7Ilg{J-n :e5jKcUa-^FCxbE_)mfoBAL9^_*MpnSG`m{' );
define( 'LOGGED_IN_SALT',   'Ra4%:EW_MW8Q9VK3)*=[-yP`iW|Jbvr-u_-a,m>yuIQG|F+nv!mm)//fdl#{`~05' );
define( 'NONCE_SALT',       'sl7#Rhmt`qiuG%S~fT1<_Z,e/Cx4m7IM(^9D+|)twe#r5{eo!gWN!fee@mf%4?vA' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
