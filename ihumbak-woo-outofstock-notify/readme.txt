=== ihumbak - Woo Out of Stock Notify ===
Contributors: michalstaniecko
Tags: woocommerce, out of stock, notify, email, stock notification
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.1
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

== Installation ==

1. Wgraj katalog `ihumbak-woo-outofstock-notify` do `/wp-content/plugins/`.
2. Aktywuj wtyczkę w panelu WordPress.
3. Przejdź do **WooCommerce → Powiadomienia o dostępności** i ustaw adres e-mail oraz tekst formularza.

== Changelog ==

= 1.0.1 =
* Aktualizacja akcji workflow Release do Node 24 (checkout v7, action-gh-release v3).

= 1.0.0 =
* Pierwsza wersja.
