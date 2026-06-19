<?php
/**
 * Obsługa żądania AJAX – wysyłka powiadomienia e-mail.
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IWON_Ajax
 *
 * Waliduje dane i wysyła e-mail do administratora. Nic nie zapisuje w bazie.
 */
class IWON_Ajax {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_' . IWON_Plugin::AJAX_ACTION, array( $this, 'handle' ) );
		add_action( 'wp_ajax_nopriv_' . IWON_Plugin::AJAX_ACTION, array( $this, 'handle' ) );
	}

	/**
	 * Obsługuje żądanie AJAX.
	 */
	public function handle() {
		// Weryfikacja nonce.
		if ( ! check_ajax_referer( IWON_Plugin::AJAX_ACTION, 'nonce', false ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Sesja wygasła. Odśwież stronę i spróbuj ponownie.', 'ihumbak-woo-outofstock-notify' ) ),
				403
			);
		}

		// Walidacja e-maila.
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( '' === $email || ! is_email( $email ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Podaj prawidłowy adres e-mail.', 'ihumbak-woo-outofstock-notify' ) ),
				400
			);
		}

		// Produkt.
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product instanceof WC_Product ) {
			wp_send_json_error(
				array( 'message' => __( 'Nie znaleziono produktu.', 'ihumbak-woo-outofstock-notify' ) ),
				404
			);
		}

		$sent = $this->send_notification( $email, $product );

		if ( ! $sent ) {
			wp_send_json_error(
				array( 'message' => __( 'Nie udało się wysłać wiadomości. Spróbuj ponownie później.', 'ihumbak-woo-outofstock-notify' ) ),
				500
			);
		}

		wp_send_json_success(
			array( 'message' => __( 'Dziękujemy! Powiadomimy Cię, kiedy produkt będzie dostępny.', 'ihumbak-woo-outofstock-notify' ) )
		);
	}

	/**
	 * Buduje i wysyła wiadomość e-mail do administratora.
	 *
	 * @param string     $customer_email E-mail klienta.
	 * @param WC_Product $product        Produkt.
	 * @return bool
	 */
	private function send_notification( $customer_email, $product ) {
		$to          = IWON_Plugin::get_notification_email();
		$blog_name   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$product_url = get_permalink( $product->get_id() );

		/* translators: %s: nazwa produktu. */
		$subject = sprintf(
			__( 'Klient czeka na produkt: %s', 'ihumbak-woo-outofstock-notify' ),
			$product->get_name()
		);

		$lines = array(
			__( 'Klient jest zainteresowany niedostępnym produktem.', 'ihumbak-woo-outofstock-notify' ),
			'',
			sprintf( '%s %s', __( 'Produkt:', 'ihumbak-woo-outofstock-notify' ), $product->get_name() ),
			sprintf( '%s %s', __( 'Link:', 'ihumbak-woo-outofstock-notify' ), $product_url ),
			sprintf( '%s %s', __( 'E-mail klienta:', 'ihumbak-woo-outofstock-notify' ), $customer_email ),
		);

		$message = implode( "\n", $lines );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s', $customer_email ),
		);

		/**
		 * Filtr pozwalający nadpisać parametry wiadomości.
		 *
		 * @param array      $args    Tablica to/subject/message/headers.
		 * @param WC_Product $product Produkt.
		 * @param string     $customer_email E-mail klienta.
		 */
		$args = apply_filters(
			'iwon_notification_email_args',
			array(
				'to'      => $to,
				'subject' => $subject,
				'message' => $message,
				'headers' => $headers,
			),
			$product,
			$customer_email
		);

		return wp_mail( $args['to'], $args['subject'], $args['message'], $args['headers'] );
	}
}
