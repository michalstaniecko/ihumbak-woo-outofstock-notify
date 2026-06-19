<?php
/**
 * Aktualizacje wtyczki z GitHub Releases.
 *
 * Czyta najnowszy release z GitHub API i podstawia zbudowany asset ZIP
 * (publikowany przez workflow Release) do natywnego mechanizmu aktualizacji
 * WordPressa. Brak zewnętrznych zależności.
 *
 * @package IhumbakWooOutofstockNotify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IWON_Updater
 */
class IWON_Updater {

	/**
	 * Ścieżka do głównego pliku wtyczki.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Repozytorium GitHub w formacie „owner/repo".
	 *
	 * @var string
	 */
	private $repo;

	/**
	 * Slug wtyczki (nazwa katalogu).
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Basename wtyczki (slug/plik.php).
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Klucz transienta cache.
	 *
	 * @var string
	 */
	private $cache_key = 'iwon_github_release';

	/**
	 * Czas życia cache (sekundy).
	 *
	 * @var int
	 */
	private $cache_ttl;

	/**
	 * Konstruktor.
	 *
	 * @param string $file Główny plik wtyczki.
	 * @param string $repo Repozytorium „owner/repo".
	 */
	public function __construct( $file, $repo ) {
		$this->file      = $file;
		$this->repo      = $repo;
		$this->basename  = plugin_basename( $file );
		$this->slug      = dirname( $this->basename );
		$this->cache_ttl = 6 * HOUR_IN_SECONDS;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_details' ), 10, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_dir' ), 10, 4 );
		add_action( 'upgrader_process_complete', array( $this, 'purge_cache' ), 10, 2 );
	}

	/**
	 * Pobiera dane najnowszego release (z cache).
	 *
	 * @return array|false
	 */
	private function get_remote_release() {
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			return is_array( $cached ) ? $cached : false;
		}

		$url      = sprintf( 'https://api.github.com/repos/%s/releases/latest', $this->repo );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			// Cache pustki na krótko, by nie wołać API przy każdym żądaniu.
			set_transient( $this->cache_key, 'none', MINUTE_IN_SECONDS * 30 );
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) || empty( $data['tag_name'] ) ) {
			set_transient( $this->cache_key, 'none', MINUTE_IN_SECONDS * 30 );
			return false;
		}

		$version     = ltrim( (string) $data['tag_name'], 'vV' );
		$package_url = $this->find_asset_url( $data );

		$release = array(
			'version'   => $version,
			'package'   => $package_url,
			'html_url'  => isset( $data['html_url'] ) ? $data['html_url'] : '',
			'changelog' => isset( $data['body'] ) ? (string) $data['body'] : '',
			'published' => isset( $data['published_at'] ) ? $data['published_at'] : '',
		);

		set_transient( $this->cache_key, $release, $this->cache_ttl );

		return $release;
	}

	/**
	 * Znajduje URL assetu ZIP (zbudowanego przez workflow).
	 *
	 * @param array $data Odpowiedź z GitHub API.
	 * @return string
	 */
	private function find_asset_url( $data ) {
		if ( empty( $data['assets'] ) || ! is_array( $data['assets'] ) ) {
			return '';
		}

		$expected = $this->slug . '.zip';

		foreach ( $data['assets'] as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && $asset['name'] === $expected ) {
				return $asset['browser_download_url'];
			}
		}

		// Fallback: pierwszy asset .zip.
		foreach ( $data['assets'] as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && '.zip' === substr( $asset['name'], -4 ) ) {
				return $asset['browser_download_url'];
			}
		}

		return '';
	}

	/**
	 * Wstrzykuje informację o dostępnej aktualizacji.
	 *
	 * @param mixed $transient Transient aktualizacji wtyczek.
	 * @return mixed
	 */
	public function check_for_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		$release = $this->get_remote_release();
		if ( ! $release || empty( $release['version'] ) || empty( $release['package'] ) ) {
			return $transient;
		}

		if ( ! version_compare( IWON_VERSION, $release['version'], '<' ) ) {
			return $transient;
		}

		$item = array(
			'id'          => $this->basename,
			'slug'        => $this->slug,
			'plugin'      => $this->basename,
			'new_version' => $release['version'],
			'url'         => $release['html_url'],
			'package'     => $release['package'],
		);

		$transient->response[ $this->basename ] = (object) $item;

		return $transient;
	}

	/**
	 * Dostarcza dane do okna „Wyświetl szczegóły".
	 *
	 * @param mixed  $result Wynik.
	 * @param string $action Akcja API.
	 * @param object $args   Argumenty.
	 * @return mixed
	 */
	public function plugin_details( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( empty( $args->slug ) || $args->slug !== $this->slug ) {
			return $result;
		}

		$release = $this->get_remote_release();
		if ( ! $release ) {
			return $result;
		}

		$data = get_plugin_data( $this->file, false, false );

		$info = array(
			'name'          => $data['Name'],
			'slug'          => $this->slug,
			'version'       => $release['version'],
			'author'        => $data['Author'],
			'homepage'      => $data['PluginURI'],
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => $data['Description'],
				'changelog'   => $this->format_changelog( $release['changelog'] ),
			),
		);

		if ( ! empty( $release['published'] ) ) {
			$info['last_updated'] = $release['published'];
		}

		return (object) $info;
	}

	/**
	 * Zamienia treść release (markdown) na prosty HTML changelog.
	 *
	 * @param string $body Treść release.
	 * @return string
	 */
	private function format_changelog( $body ) {
		$body = trim( wp_strip_all_tags( $body ) );
		if ( '' === $body ) {
			return esc_html__( 'Brak informacji o zmianach.', 'ihumbak-woo-outofstock-notify' );
		}
		return wpautop( esc_html( $body ) );
	}

	/**
	 * Gwarantuje, że rozpakowany katalog ma nazwę slug wtyczki.
	 *
	 * @param string      $source        Ścieżka źródłowa.
	 * @param string      $remote_source Zdalna ścieżka.
	 * @param WP_Upgrader $upgrader      Upgrader.
	 * @param array       $hook_extra    Dodatkowe dane.
	 * @return string|WP_Error
	 */
	public function fix_source_dir( $source, $remote_source, $upgrader, $hook_extra = array() ) {
		global $wp_filesystem;

		if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->basename ) {
			return $source;
		}

		if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
			return $source;
		}

		$desired = trailingslashit( $remote_source ) . $this->slug;
		$source  = untrailingslashit( $source );

		if ( $source === untrailingslashit( $desired ) ) {
			return trailingslashit( $source );
		}

		if ( $wp_filesystem->move( $source, $desired, true ) ) {
			return trailingslashit( $desired );
		}

		return $source;
	}

	/**
	 * Czyści cache po zakończonej aktualizacji.
	 *
	 * @param WP_Upgrader $upgrader Upgrader.
	 * @param array       $options  Opcje.
	 */
	public function purge_cache( $upgrader, $options ) {
		if ( isset( $options['action'], $options['type'] ) && 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			delete_transient( $this->cache_key );
		}
	}
}
