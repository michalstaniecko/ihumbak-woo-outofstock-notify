# ihumbak - Woo Out of Stock Notify

Wtyczka WooCommerce: na stronie niedostępnego produktu wyświetla formularz e-mail,
po wysłaniu którego administrator sklepu dostaje powiadomienie o zainteresowaniu klienta.
Dane nie są zapisywane w bazie.

## Struktura repozytorium

Źródła wtyczki znajdują się w podkatalogu `ihumbak-woo-outofstock-notify/`, aby ułatwić
konfigurację `wordpress-setup-github-release` oraz `wordpress-setup-plugin-updater`
(do paczki ZIP trafia tylko ten katalog, bez plików repo).

```
.
├── README.md                              # ten plik (poziom repo)
└── ihumbak-woo-outofstock-notify/         # ← źródła wtyczki (pakowane do ZIP)
    ├── ihumbak-woo-outofstock-notify.php  # główny plik + nagłówek wtyczki
    ├── includes/
    │   ├── class-iwon-plugin.php          # bootstrap, zależność WooCommerce
    │   ├── class-iwon-settings.php        # strona ustawień (e-mail + tekst)
    │   ├── class-iwon-frontend.php        # formularz dla produktów out of stock
    │   ├── class-iwon-ajax.php            # walidacja + wp_mail (bez zapisu w DB)
    │   └── class-iwon-updater.php         # aktualizacje z GitHub Releases
    ├── assets/
    │   ├── css/iwon-form.css
    │   └── js/iwon-form.js
    └── readme.txt
```

## Konfiguracja

Po aktywacji: **WooCommerce → Powiadomienia o dostępności**
— adres e-mail powiadomień oraz tekst nad formularzem.

## Aktualizacje

Wtyczka aktualizuje się z **GitHub Releases**. `IWON_Updater` odpytuje GitHub API
o najnowszy release repozytorium `IWON_GITHUB_REPO`
(domyślnie `michalstaniecko/ihumbak-woo-outofstock-notify`), porównuje wersję
z nagłówkiem wtyczki i podstawia zbudowany asset `ihumbak-woo-outofstock-notify.zip`
do natywnego mechanizmu aktualizacji WordPressa. Odpowiedź API jest cache'owana
w transiencie (6 h). Aby nadpisać repozytorium, zdefiniuj `IWON_GITHUB_REPO`
w `wp-config.php` przed załadowaniem wtyczki.

Wydanie nowej wersji:

```bash
# 1. podbij Version: w nagłówku wtyczki i Stable tag w readme.txt
# 2. otaguj i wypchnij — workflow Release zbuduje ZIP i opublikuje release
git tag v1.0.1
git push origin v1.0.1
```
