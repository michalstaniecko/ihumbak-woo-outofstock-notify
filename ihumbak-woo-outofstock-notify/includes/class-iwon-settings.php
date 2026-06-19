<?php
/**
 * Strona ustawień wtyczki.
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IWON_Settings
 *
 * Rejestruje stronę ustawień (pod menu WooCommerce) oraz pola Settings API.
 */
class IWON_Settings {

	/**
	 * Slug strony ustawień.
	 */
	const PAGE_SLUG = 'iwon-settings';

	/**
	 * Grupa ustawień.
	 */
	const OPTION_GROUP = 'iwon_settings_group';

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( IWON_PLUGIN_FILE ),
			array( $this, 'add_settings_link' )
		);
	}

	/**
	 * Dodaje podstronę w menu WooCommerce.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Powiadomienia o dostępności', 'ihumbak-woo-outofstock-notify' ),
			__( 'Powiadomienia o dostępności', 'ihumbak-woo-outofstock-notify' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Link „Ustawienia" na liście wtyczek.
	 *
	 * @param array $links Istniejące linki.
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$url  = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Ustawienia', 'ihumbak-woo-outofstock-notify' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Rejestruje opcje i pola.
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			IWON_Plugin::OPTION_EMAIL,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_email_field' ),
				'default'           => get_option( 'admin_email' ),
			)
		);

		register_setting(
			self::OPTION_GROUP,
			IWON_Plugin::OPTION_INTRO,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => IWON_Plugin::default_intro_text(),
			)
		);

		add_settings_section(
			'iwon_main_section',
			__( 'Ustawienia powiadomień', 'ihumbak-woo-outofstock-notify' ),
			array( $this, 'render_section_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			IWON_Plugin::OPTION_EMAIL,
			__( 'Adres e-mail powiadomień', 'ihumbak-woo-outofstock-notify' ),
			array( $this, 'render_email_field' ),
			self::PAGE_SLUG,
			'iwon_main_section'
		);

		add_settings_field(
			IWON_Plugin::OPTION_INTRO,
			__( 'Tekst nad formularzem', 'ihumbak-woo-outofstock-notify' ),
			array( $this, 'render_intro_field' ),
			self::PAGE_SLUG,
			'iwon_main_section'
		);
	}

	/**
	 * Sanityzacja adresu e-mail. Pusty = powrót do admin_email.
	 *
	 * @param string $value Wartość z formularza.
	 * @return string
	 */
	public function sanitize_email_field( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return get_option( 'admin_email' );
		}

		$email = sanitize_email( $value );

		if ( ! is_email( $email ) ) {
			add_settings_error(
				IWON_Plugin::OPTION_EMAIL,
				'iwon_invalid_email',
				__( 'Podany adres e-mail jest nieprawidłowy. Zachowano poprzednią wartość.', 'ihumbak-woo-outofstock-notify' ),
				'error'
			);
			$previous = get_option( IWON_Plugin::OPTION_EMAIL );
			return $previous ? $previous : get_option( 'admin_email' );
		}

		return $email;
	}

	/**
	 * Opis sekcji.
	 */
	public function render_section_intro() {
		echo '<p>' . esc_html__( 'Skonfiguruj formularz wyświetlany na stronie niedostępnych produktów oraz adres, na który trafiają powiadomienia.', 'ihumbak-woo-outofstock-notify' ) . '</p>';
	}

	/**
	 * Pole adresu e-mail.
	 */
	public function render_email_field() {
		$value = get_option( IWON_Plugin::OPTION_EMAIL, get_option( 'admin_email' ) );
		printf(
			'<input type="email" name="%1$s" id="%1$s" value="%2$s" class="regular-text" />',
			esc_attr( IWON_Plugin::OPTION_EMAIL ),
			esc_attr( $value )
		);
		echo '<p class="description">' . esc_html__( 'Na ten adres wysyłane będą powiadomienia o zainteresowaniu produktem. Pozostaw puste, aby użyć adresu administratora sklepu.', 'ihumbak-woo-outofstock-notify' ) . '</p>';
	}

	/**
	 * Pole tekstu nad formularzem.
	 */
	public function render_intro_field() {
		$value = IWON_Plugin::get_intro_text();
		printf(
			'<textarea name="%1$s" id="%1$s" rows="3" class="large-text">%2$s</textarea>',
			esc_attr( IWON_Plugin::OPTION_INTRO ),
			esc_textarea( $value )
		);
		echo '<p class="description">' . esc_html__( 'Tekst wyświetlany nad formularzem na stronie produktu.', 'ihumbak-woo-outofstock-notify' ) . '</p>';
	}

	/**
	 * Renderuje stronę ustawień.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
