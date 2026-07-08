=== Plogins Pickup - Local Pickup for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Wymaga wtyczek: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pozwól klientom wybrać miejsce odbioru i przedział czasowy przy kasie.

== Description ==

Pickup dodaje harmonogram „kliknij i odbierz” do WooCommerce. Kiedy zamówienie korzysta
WooCommerce <strong>Odbiór lokalny</strong>, klient wybiera <strong>lokalizację</strong> odbioru i
<strong>data + przedział czasowy</strong> bezpośrednio przy kasie. Wybór zostaje zatwierdzony i zapisany w pliku
zamówienie i wyświetlane na ekranie zamówienia administratora oraz w wiadomościach e-mail z zamówieniem.

Automaty są generowane na podstawie zdefiniowanych przez Ciebie tygodniowych okien otwarcia, przy użyciu wybranych przez Ciebie
długość slotu, minimalny czas realizacji i horyzont rezerwacji. Każde gniazdo ma pojemność: raz
lokalizacja i czas osiągną tę liczbę zamówień, zostanie ona usunięta z listy
nie można zarezerwować dwukrotnie.

Wszystko jest przechowywane jako meta zamówienia, więc nie ma niestandardowej tabeli bazy danych
utrzymać. Pola kasy pojawiają się tylko w przypadku wybrania opcji Odbiór lokalny; dla
w przypadku każdej innej metody wysyłki pozostają one ukryte i nigdy nie są wymagane.

Picker żyje na klasycznej kasie. Z blokowym koszykiem i kasą
wtyczka deklaruje kompatybilność i zapisane dane odbioru nadal pojawiają się na zamówieniu,
e-maile i strony konta, ale interfejs użytkownika pola w kasie jest klasyczny.

Kod źródłowy i raporty o błędach: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-pickup/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-pickup/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-pickup
* <strong>Raporty o błędach i prośby o nowe funkcje</strong> - https://github.com/wppoland/plogins-pickup/issues


= Features =

* Wybór lokalizacji odbioru przy kasie (lista zdefiniowana przez administratora, włącz/wyłącz każdą).
* Wybór daty i przedziału czasowego na podstawie tygodniowych godzin otwarcia.
* Konfigurowalna długość slotu, pojemność na slot, czas realizacji i horyzont rezerwacji.
* Terminy, które są zajęte lub mieszczą się w oknie czasowym realizacji, są usuwane z listy.
* Przed utworzeniem zamówienia wybór jest ponownie sprawdzany na serwerze.
* Szczegóły odbioru pokazane na ekranie zamówienia administratora, w wiadomościach e-mail dotyczących zamówienia oraz na stronie
  zamówienia klienta i strony z podziękowaniami.
* Podczas wyświetlania daty wykorzystuje strefę czasową sklepu i format daty WordPress.
* Żadnych niestandardowych tabel i żadnych połączeń z usługami zewnętrznymi.
* Dostarczany z plikiem POT do tłumaczenia i usuwa jego ustawienia po odinstalowaniu.
* Deklaruje kompatybilność z HPOS i współpracuje z blokami Koszyka i Kasy.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/pickup` lub zainstaluj poprzez Wtyczki → Dodaj nową.
2. Aktywuj. WooCommerce musi być aktywny.
3. Upewnij się, że opcja WooCommerce <strong>Odbiór lokalny<strong> jest włączona w WooCommerce → Ustawienia → Wysyłka. 4. Przejdź do </strong>WooCommerce → Odbiór</strong>, dodaj swoje lokalizacje i tygodniowe godziny otwarcia, a następnie
   ustaw długość slotu, pojemność, czas realizacji i horyzont rezerwacji.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowany i aktywny, z opcją wysyłki Lokalny odbiór.

= When do the pickup fields show at checkout? =

Tylko wtedy, gdy wybraną przez klienta metodą wysyłki jest odbiór lokalny WooCommerce. Dla
w przypadku wszystkich innych metod pola pozostają ukryte i nie są wymagane.

= How are time slots generated? =

Na podstawie tygodniowych okien otwarcia i długości przedziału czasowego. Na przykład 09:00–12:00
okno z 30-minutowym przedziałem czasu oferuje godziny 09:00, 09:30, 10:00 i tak dalej.

= What stops a slot from being over-booked? =

Każde gniazdo ma swoją pojemność. Jednorazowo liczba zamówień zarezerwowanych w lokalizacji + data
+ slot osiągnie tę pojemność, slot nie będzie już oferowany.

= Does it create database tables? =

Nie. Wybrane pozycje są przechowywane jako meta zamówienia, więc nie trzeba niczego więcej konserwować.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Na wystawie sklepowej.
2. Ustawienia w panelu administracyjnym WordPress.
3. Na urządzeniu mobilnym.
== External Services ==

Odbiór nie łączy się z żadnymi usługami zewnętrznymi. Przeglądanie przedziałów czasowych na żywo pod adresem
pobieraj wpisy do punktu końcowego AJAX WordPress swojej własnej witryny (`admin-ajax.php`) i
sloty są obliczane na Twoim serwerze na podstawie skonfigurowanych przez Ciebie godzin otwarcia. Twój
ustawienia znajdują się w opcji „pickup_settings”, a wybór każdego zamówienia jest przechowywany jako
meta zamówienia (`_lokalizacja_odbioru`, `_data_odbioru`, `_slot_odbioru`); szczegóły odbioru są
pokazywane poprzez dodanie ich do własnych e-maili z zamówieniami WooCommerce, a nie poprzez wysłanie jakiejkolwiek poczty
własne. Żadne dane nie opuszczają Twojej witryny.

== Changelog ==

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.3 =
* Zmieniono nazwę na Plogins Pickup dla WooCommerce, aby uzyskać bardziej charakterystyczną nazwę wtyczki.

= 0.1.2 =
* Filtr „pickup/slot_fee” dla opcjonalnych opłat lub rabatów za koszyk.
* Opłata za koszyk naliczana przy kasie, gdy wybrany jest płatny przedział; Lista automatów AJAX zawiera kwoty opłat.

= 0.1.1 =
* Haki przedłużające dla Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `odbiór/data_dostępności`, `odbiór/dostępność_slotu`, `odbiór/daty_zablokowania`.
* Skrypt Checkout honoruje zablokowane daty przekazane z serwera.

= 0.1.0 =
* Pierwsza wersja: wybór miejsca odbioru i wybór daty/godziny przy kasie,
  tygodniowe planowanie godzin otwarcia z uwzględnieniem długości przedziału czasowego, wydajności, czasu realizacji i
  horyzont rezerwacji, wyświetlacz zamówienia + e-mail oraz ekran ustawień WooCommerce.
