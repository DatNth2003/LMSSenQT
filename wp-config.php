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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '|I2tDD*ksT`{m>kePO3RYtZ~[fAYWX+>B86B|7OY~X*sq:Ec/c =2rH@#*C*^IMZ' );
define( 'SECURE_AUTH_KEY',   '+7RS4*pO9$rf(, CWlnpqpg+F b>^W3Y.&X9WH~{j1q%,$&1i:UKL,Y?Ii5.LjcX' );
define( 'LOGGED_IN_KEY',     'wVy?G@t+mslLbU`?%)7nBKBPa(^g!6V}gL&6]XuO+2632J#] HeH&TuuEQ)H6c* ' );
define( 'NONCE_KEY',         'x?6p8OyA-!esai0$#k}sZ{-V# []ly4aph2uy@qNL+Ag-MxYiWpr;^Vu2n5MuxxQ' );
define( 'AUTH_SALT',         '9g7feIL.D+..;?Fz45mRW`o*;FVV?s^<Xf,uvjQXC7fJtDIjNv:a/RZ*p %b.Fi7' );
define( 'SECURE_AUTH_SALT',  '7Ffp*CK:%Zd6JbxVY(lun%<sT%*Nd5sf#$80.o:z^s5k7@[WJe6Y~vVC*[3<L(%a' );
define( 'LOGGED_IN_SALT',    '_l6q+lVCd+R&M6<^&1$(nKO9K*^@Kb!_|o+@_p#Sc)scC1!Wa.;_bbWj;uSy.-QM' );
define( 'NONCE_SALT',        '1-B]e1}@3oXQD1D$4|>PMI~X<$E,{Cuw}[-0]rYS!IJ;?]Mv8DI/Z9I`*qpoAciA' );
define( 'WP_CACHE_KEY_SALT', 'h<|Qf2dJaJYvkWy?B6Q@nTc+@-7|K$xSl+#aUl<xCcg<^PNlqkVPwEW=bIN:*EU}' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
