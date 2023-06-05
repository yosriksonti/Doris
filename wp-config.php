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

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '' );

/** MySQL database username */
define( 'DB_USER', '' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', '' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'R24JG$@1Y$qt>j0UJ%iiwq[?%0yITS1eG5r()VQz-L8>o^{009#$[Qk+H(:{,Xus' );
define( 'SECURE_AUTH_KEY',   'F`wT@uPYv^N5,8{PIeYD)s5zQ$z|;(J|hNa8Ivgvda{!~nTkmK[gTd|+-#S{Q5_-' );
define( 'LOGGED_IN_KEY',     '1!q~[G4{ti4X4(0@1@; PC4*X} *1t?`Eo`J-e0wUn?C3%E{3?o|]7po/mza%XTi' );
define( 'NONCE_KEY',         'aTRO<VH8x5cMA[ZJYf]Bu.(?zkZj?-!894q<yxRX!s]O+L2/xu(Qi_/$B}EFXfrN' );
define( 'AUTH_SALT',         '+y,Q-:s#sIyvtv(9Cc0q!%SRD;2;4iO(/XM(A3]V^7Gz+K6+4$m.nR+W%kp)H.z4' );
define( 'SECURE_AUTH_SALT',  'YLnCeYf/_t|[*WxmR69e||M:-B>f_%a5#3EwM1YQmwMd=LIhZRPs{IpyWFDw<`@!' );
define( 'LOGGED_IN_SALT',    '>])Havr,^,]nAhGn}rXZ`fIub6V:ezR^x.3%m9/b+3>y,0+nE#>bB%I@.Ir19A1&' );
define( 'NONCE_SALT',        'Lg4+sxDvHM2P3V/izdUy-*KIB>NCbTq5U.H>5N}jkY[Gy2ksuF,a)IdHn>MB{aDe' );
define( 'WP_CACHE_KEY_SALT', '}?y22F4Uo8Qc-#+znb+#K;Hzqqj`3DpE}kX4hrKZ,8[$V$.>pcOf)8HcjIrr}hD#' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'bk2_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
