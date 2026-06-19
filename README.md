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
    │   └── class-iwon-ajax.php            # walidacja + wp_mail (bez zapisu w DB)
    ├── assets/
    │   ├── css/iwon-form.css
    │   └── js/iwon-form.js
    └── readme.txt
```

## Konfiguracja

Po aktywacji: **WooCommerce → Powiadomienia o dostępności**
— adres e-mail powiadomień oraz tekst nad formularzem.
