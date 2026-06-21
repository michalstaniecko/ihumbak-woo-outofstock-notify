=== ihumbak - Woo Out of Stock Notify ===
Contributors: michalstaniecko
Tags: woocommerce, out of stock, notify, email, stock notification
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Wyświetla na stronie niedostępnego produktu prosty formularz e-mail. Administrator otrzymuje powiadomienie o zainteresowaniu klienta. Bez zapisu w bazie.

== Description ==

Wtyczka dla WooCommerce, która na stronie produktu oznaczonego jako *niedostępny* (out of stock) wyświetla – w miejscu przycisku „dodaj do koszyka" – prosty formularz: pole e-mail + przycisk.

Po wysłaniu formularza administrator sklepu otrzymuje wiadomość e-mail z informacją, którym produktem klient jest zainteresowany oraz z adresem e-mail klienta. **Żadne dane nie są zapisywane w bazie danych** – wtyczka jedynie wysyła powiadomienie.

= Funkcje =

* Formularz tylko dla produktów niedostępnych.
* Wysyłka przez AJAX – bez przeładowania strony.
* Komunikat „wysyłanie…" oraz potwierdzenie wysłania pod formularzem.
* Walidacja adresu e-mail (po stronie przeglądarki i serwera).
* Konfigurowalny adres e-mail powiadomień (domyślnie adres administratora).
* Konfigurowalny tekst wyświetlany nad formularzem.
* Prosty, neutralny layout dopasowujący się do różnych motywów.
* Opcjonalna ochrona przed botami przez wtyczkę „hCaptcha for WP" (gdy aktywna).

== Installation ==

1. Wgraj katalog `ihumbak-woo-outofstock-notify` do `/wp-content/plugins/`.
2. Aktywuj wtyczkę w panelu WordPress.
3. Przejdź do **WooCommerce → Powiadomienia o dostępności** i ustaw adres e-mail oraz tekst formularza.

== Changelog ==

= 1.2.4 =
* Naprawiono podwójny komunikat po wysłaniu formularza oraz fałszywy komunikat sukcesu pojawiający się mimo nieprzejścia hCaptcha. Wyłączono własny tryb AJAX hCaptcha for WP – wysyłkę i weryfikację obsługuje wyłącznie wtyczka.

= 1.2.3 =
* Naprawiono brak przełącznika „Włącz automatyczne aktualizacje" na liście wtyczek. Updater rejestruje teraz wtyczkę w transiencie aktualizacji również wtedy, gdy jest aktualna (gałąź no_update), dzięki czemu WordPress pokazuje opcję automatycznych aktualizacji.

= 1.2.2 =
* Formularz układa się teraz w jednej kolumnie (pole e-mail, hCaptcha, przycisk jeden pod drugim) z użyciem CSS grid, co poprawia wygląd przy aktywnym widgecie hCaptcha.
* Wyzerowano dolny margines widgetu hCaptcha oraz dodano style tekstu nad formularzem (rozmiar 14 px, pogrubienie).

= 1.2.1 =
* Naprawiono błąd „Anti-spam check failed." przy wysyłce formularza z aktywną wtyczką „hCaptcha for WP". Formularz wysyła teraz wszystkie pola hCaptcha (w tym ukryte pola anti-spam), zamiast wybranych, dzięki czemu weryfikacja przechodzi poprawnie.

= 1.2.0 =
* Dodano norweskie tłumaczenie (Bokmål, nb_NO) wraz z plikiem szablonu .pot.
* Komunikaty zwracane klientowi przez AJAX (m.in. potwierdzenie wysłania) oraz treść powiadomienia e-mail są teraz renderowane w języku sklepu, niezależnie od języka profilu zalogowanego użytkownika.

= 1.1.0 =
* Integracja z wtyczką „hCaptcha for WP" – opcjonalna ochrona formularza przed botami. Widget pojawia się i jest weryfikowany tylko, gdy wtyczka hCaptcha jest aktywna; w przeciwnym razie formularz działa bez zmian.

= 1.0.2 =
* Deklaracja zgodności z WooCommerce HPOS (High-Performance Order Storage) – usuwa komunikat o niezgodności wtyczki.

= 1.0.1 =
* Aktualizacja akcji workflow Release do Node 24 (checkout v7, action-gh-release v3).

= 1.0.0 =
* Pierwsza wersja.
