<?php
/**
 * Główna klasa bootstrap wtyczki.
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IWON_Plugin
 *
 * Inicjalizuje pozostałe komponenty wtyczki i pilnuje zależności od WooCommerce.
 */
final class IWON_Plugin {

	/**
	 * Nazwa opcji przechowującej adres e-mail powiadomień.
	 */
	const OPTION_EMAIL = 'iwon_notification_email';

	/**
	 * Nazwa opcji przechowującej tekst zachęty nad formularzem.
	 */
	const OPTION_INTRO = 'iwon_intro_text';

	/**
	 * Akcja AJAX.
	 */
	const AJAX_ACTION = 'iwon_notify';

	/**
	 * Pojedyncza instancja.
	 *
	 * @var IWON_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Zwraca instancję (singleton).
	 *
	 * @return IWON_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Konstruktor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Aktualizacje z GitHub – działają niezależnie od WooCommerce.
		if ( defined( 'IWON_GITHUB_REPO' ) && IWON_GITHUB_REPO ) {
			new IWON_Updater( IWON_PLUGIN_FILE, IWON_GITHUB_REPO );
		}

		// Pozostała funkcjonalność wymaga WooCommerce.
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'render_missing_woocommerce_notice' ) );
			return;
		}

		new IWON_Settings();
		new IWON_Frontend();
		new IWON_Ajax();
	}

	/**
	 * Domyślny tekst zachęty.
	 *
	 * @return string
	 */
	public static function default_intro_text() {
		return __( 'Zostaw email i powiadomimy Cię, kiedy produkt będzie dostępny.', 'ihumbak-woo-outofstock-notify' );
	}

	/**
	 * Adres e-mail, na który wysyłane są powiadomienia.
	 *
	 * @return string
	 */
	public static function get_notification_email() {
		$email = get_option( self::OPTION_EMAIL, '' );
		$email = is_string( $email ) ? trim( $email ) : '';

		if ( '' === $email || ! is_email( $email ) ) {
			$email = get_option( 'admin_email' );
		}

		return $email;
	}

	/**
	 * Tekst zachęty wyświetlany nad formularzem.
	 *
	 * @return string
	 */
	public static function get_intro_text() {
		$text = get_option( self::OPTION_INTRO, '' );
		$text = is_string( $text ) ? trim( $text ) : '';

		if ( '' === $text ) {
			$text = self::default_intro_text();
		}

		return $text;
	}

	/**
	 * Czy WooCommerce jest aktywne.
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Ładuje tłumaczenia.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ihumbak-woo-outofstock-notify',
			false,
			dirname( plugin_basename( IWON_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Komunikat w panelu, gdy brakuje WooCommerce.
	 */
	public function render_missing_woocommerce_notice() {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Wtyczka „ihumbak - Woo Out of Stock Notify" wymaga aktywnego WooCommerce.', 'ihumbak-woo-outofstock-notify' );
		echo '</p></div>';
	}
}
