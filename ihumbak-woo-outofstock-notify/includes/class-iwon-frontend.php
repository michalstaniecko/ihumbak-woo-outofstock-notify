<?php
/**
 * Obsługa frontendu – formularz na stronie produktu.
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IWON_Frontend
 *
 * Renderuje formularz w miejscu „dodaj do koszyka" dla produktów niedostępnych.
 */
class IWON_Frontend {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		// Priorytet 31 – tuż po standardowym miejscu „dodaj do koszyka" (prio 30).
		add_action( 'woocommerce_single_product_summary', array( $this, 'maybe_render_form' ), 31 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Renderuje formularz, jeśli produkt jest niedostępny (out of stock).
	 */
	public function maybe_render_form() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		// Tylko dla produktów niedostępnych.
		if ( $product->is_in_stock() ) {
			return;
		}

		$this->render_form( $product );
	}

	/**
	 * Wczytuje style i skrypty tylko na stronie produktu.
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'iwon-form',
			IWON_PLUGIN_URL . 'assets/css/iwon-form.css',
			array(),
			IWON_VERSION
		);

		wp_enqueue_script(
			'iwon-form',
			IWON_PLUGIN_URL . 'assets/js/iwon-form.js',
			array(),
			IWON_VERSION,
			true
		);

		wp_localize_script(
			'iwon-form',
			'iwonData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => IWON_Plugin::AJAX_ACTION,
				'nonce'   => wp_create_nonce( IWON_Plugin::AJAX_ACTION ),
				'i18n'    => array(
					'sending'      => __( 'Wysyłanie…', 'ihumbak-woo-outofstock-notify' ),
					'success'      => __( 'Dziękujemy! Powiadomimy Cię, kiedy produkt będzie dostępny.', 'ihumbak-woo-outofstock-notify' ),
					'errorGeneric' => __( 'Coś poszło nie tak. Spróbuj ponownie później.', 'ihumbak-woo-outofstock-notify' ),
					'invalidEmail' => __( 'Podaj prawidłowy adres e-mail.', 'ihumbak-woo-outofstock-notify' ),
				),
			)
		);
	}

	/**
	 * Wypisuje HTML formularza.
	 *
	 * @param WC_Product $product Produkt.
	 */
	private function render_form( $product ) {
		$intro    = IWON_Plugin::get_intro_text();
		$field_id = 'iwon-email-' . absint( $product->get_id() );
		?>
		<div class="iwon-notify" data-iwon-product="<?php echo esc_attr( $product->get_id() ); ?>">
			<p class="iwon-notify__intro"><?php echo esc_html( $intro ); ?></p>
			<form class="iwon-notify__form" novalidate>
				<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>">
					<?php esc_html_e( 'Adres e-mail', 'ihumbak-woo-outofstock-notify' ); ?>
				</label>
				<input
					type="email"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="iwon_email"
					class="iwon-notify__input"
					placeholder="<?php esc_attr_e( 'Twój adres e-mail', 'ihumbak-woo-outofstock-notify' ); ?>"
					required
				/>
				<button type="submit" class="iwon-notify__submit button">
					<?php esc_html_e( 'Powiadom mnie', 'ihumbak-woo-outofstock-notify' ); ?>
				</button>
				<p class="iwon-notify__message" role="status" aria-live="polite"></p>
			</form>
		</div>
		<?php
	}
}
