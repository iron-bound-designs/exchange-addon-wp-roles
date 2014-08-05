<?php
/*
 * Plugin Name: iThemes Exchange - Assign WP Roles Add-on
 * Plugin URI: http://ironbounddesigns.com
 * Description: Assign WP Roles when a product is purchased
 * Version: 0.9.1
 * Author: Iron Bound Designs
 * Author URI: http://ironbounddesigns.com
 * License: GPL2
 */

/**
 * This registers our plugin as a coinbase addon
 *
 * To learn how to create your own-addon, visit http://ithemes.com/codex/page/Exchange_Custom_Add-ons:_Overview
 *
 * @since 1.0
 *
 * @return void
 */
function it_exchange_register_wp_roles_addon() {
	$options = array(
		'name'              => __( 'Assign WP Roles', 'it-l10n-exchange-addon-wp-roles' ),
		'description'       => __( 'Assign WP roles when a product is purchased.', 'it-l10n-exchange-addon-wp-roles' ),
		'author'            => 'Iron Bound Designs',
		'author_url'        => 'http://ironbounddesigns.com',
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'product-feature',
	);
	it_exchange_register_addon( 'wp-roles', $options );
}

add_action( 'it_exchange_register_addons', 'it_exchange_register_wp_roles_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses  load_plugin_textdomain()
 * @since 1.0
 * @return void
 */
function it_exchange_wp_roles_set_textdomain() {
	load_plugin_textdomain( 'it-l10n-exchange-addon-wp-roles', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

add_action( 'plugins_loaded', 'it_exchange_wp_roles_set_textdomain' );

/**
 * Class IT_Exchange_Coinbase
 */
class IT_Exchange_WP_Roles_Addon {

	/**
	 * Translation slug
	 */
	const SLUG = 'it-l10n-exchange-addon-wp-roles';

	/**
	 * @var string $dir
	 */
	public static $dir;
	/**
	 * @var string $url
	 */
	public static $url;

	/**
	 *
	 */
	public function __construct() {
		self::$url = plugin_dir_url( __FILE__ );
		self::$dir = plugin_dir_path( __FILE__ );

		spl_autoload_register( array( $this, "autoload" ) );
	}

	/**
	 * Class autoloader
	 *
	 * @param $class_name string
	 */
	public function autoload( $class_name ) {
		if ( substr( $class_name, 0, 6 ) != "ITEWPR" ) {
			return;
		}

		$path = self::$dir . "lib";

		$class = substr( $class_name, 6 );
		$class = strtolower( $class );

		$parts = explode( "_", $class );
		$name  = array_pop( $parts );

		$path .= implode( "/", $parts );
		$path .= "/class.$name.php";

		if ( file_exists( $path ) ) {
			require( $path );

			return;
		}

		if ( file_exists( str_replace( "class.", "abstract.", $path ) ) ) {
			require( str_replace( "class.", "abstract.", $path ) );

			return;
		}

		if ( file_exists( str_replace( "class.", "interface.", $path ) ) ) {
			require( str_replace( "class.", "interface.", $path ) );

			return;
		}
	}
}
new IT_Exchange_WP_Roles_Addon();