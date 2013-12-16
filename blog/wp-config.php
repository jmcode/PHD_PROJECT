<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pdh_test');

/** MySQL database username */
define('DB_USER', 'phdcleanse');

/** MySQL database password */
define('DB_PASSWORD', 'MNf98m3LI#gf');

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
define('AUTH_KEY',         'BqvXdWVet-|?Vv]&8%o-] 1>#H!hQ<TUm_yl>gZ^ #=|-s#N`xe+BJsDjo8vH+~&');
define('SECURE_AUTH_KEY',  'FaESf.*%0lXP36=Q>|K->d{{%_Ze@i[`u/9{I^;y6e)1H1E+`C(qy10@AJJ+qQYy');
define('LOGGED_IN_KEY',    'o L-PJaYNBl#x:{i9@hEGCW/^Ly$4ZsPp.I#b3.[5(,-xRyUoB4|{-qcKVBr|C!`');
define('NONCE_KEY',        'x=|1iVw_?XERW):2m.!Cuu|}?k<1Yk2^n&i(2&s2XU!]T#%LW.<I3^%87Bxa=7]{');
define('AUTH_SALT',        'EtoO`U85xU&-b_=(URK*r,J?|Rb|tF$kc?+z=Jl^kOQOTR?zqEaWARz.>9e<DE>M');
define('SECURE_AUTH_SALT', 'l$gdR9@Ji){8;nx$7$}f5e@A@w)`rACb?Y`M1*El,?zRZI@OO1,g7*y>T:$}d~Y+');
define('LOGGED_IN_SALT',   'u}Uo~W-iGc.4y|R>|12+L;T4Cxn[|oxJ*[(U{jf|F3P)5bY$b%*DIdj*&-6}$ =?');
define('NONCE_SALT',       '<-d![>qK>tWflQQ#Jn4/2c|QL(:MXf0~e&lI+^em/N(#>%[t,Pqcw/{@OG`f+rW4');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
