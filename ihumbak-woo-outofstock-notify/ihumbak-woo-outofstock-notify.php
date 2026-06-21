<?php
/**
 * Plugin Name:       ihumbak - Woo Out of Stock Notify
 * Plugin URI:        https://github.com/michalstaniecko/ihumbak-woo-outofstock-notify
 * Description:       Wyświetla na stronie produktu (niedostępnego / out of stock) prosty formularz, dzięki któremu klient może zostawić swój email. Administrator sklepu otrzymuje powiadomienie e-mail o zainteresowaniu produktem. Nic nie jest zapisywane w bazie danych.
 * Version:           1.2.4
 * Author:            michalstaniecko
 * Author URI:        https://github.com/michalstaniecko
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ihumbak-woo-outofstock-notify
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 * WC tested up to:   9.0
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Bezpośredni dostęp zabroniony.
}

define( 'IWON_VERSION', '1.2.4' );
define( 'IWON_PLUGIN_FILE', __FILE__ );
define( 'IWON_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IWON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Repozytorium GitHub używane przez mechanizm aktualizacji.
if ( ! defined( 'IWON_GITHUB_REPO' ) ) {
	define( 'IWON_GITHUB_REPO', 'michalstaniecko/ihumbak-woo-outofstock-notify' );
}

require_once IWON_PLUGIN_DIR . 'includes/class-iwon-plugin.php';
require_once IWON_PLUGIN_DIR . 'includes/class-iwon-settings.php';
require_once IWON_PLUGIN_DIR . 'includes/class-iwon-frontend.php';
require_once IWON_PLUGIN_DIR . 'includes/class-iwon-ajax.php';
require_once IWON_PLUGIN_DIR . 'includes/class-iwon-updater.php';

/**
 * Uruchomienie wtyczki.
 */
function iwon_init() {
	IWON_Plugin::instance();
}
add_action( 'plugins_loaded', 'iwon_init' );

/**
 * Deklaracja zgodności z funkcjami WooCommerce.
 *
 * Wtyczka nie korzysta bezpośrednio z tabel zamówień, więc jest w pełni
 * zgodna z HPOS (High-Performance Order Storage / custom order tables).
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				IWON_PLUGIN_FILE,
				true
			);
		}
	}
);
