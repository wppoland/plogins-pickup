=== Plogins Pickup - Local Pickup for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pozwól klientom wybrać miejsce i termin odbioru osobistego przy kasie.

== Description ==

Pickup dodaje do WooCommerce planowanie odbioru w modelu „kliknij i odbierz”. Gdy
zamówienie korzysta z metody WooCommerce <strong>Odbiór osobisty</strong>, klient wybiera
<strong>miejsce</strong> odbioru oraz <strong>datę i przedział czasowy</strong> bezpośrednio przy kasie. Wybór jest
weryfikowany, zapisywany w zamówieniu i pokazywany na ekranie zamówienia w panelu oraz w e-mailach z zamówieniem.

Przedziały czasowe są generowane na podstawie zdefiniowanych przez Ciebie tygodniowych godzin otwarcia,
z uwzględnieniem wybranej długości przedziału, minimalnego czasu wyprzedzenia i horyzontu rezerwacji. Każdy
przedział ma pojemność: gdy dane miejsce i godzina osiągną tę liczbę zamówień, znikają z listy, więc
nie da się ich zarezerwować dwukrotnie.

Wszystko jest przechowywane jako metadane zamówienia, więc nie ma żadnej dodatkowej tabeli bazy danych do
utrzymania. Pola przy kasie pojawiają się tylko wtedy, gdy wybrano Odbiór osobisty; przy
każdej innej metodzie wysyłki pozostają ukryte i nigdy nie są wymagane.

Selektor działa w klasycznej kasie. W przypadku blokowego Koszyka i Kasy wtyczka deklaruje
zgodność, a zapisane szczegóły odbioru nadal pojawiają się w zamówieniu, e-mailach i na stronach
konta, ale interfejs pola w kasie jest klasyczny.

Kod źródłowy i zgłoszenia błędów: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-pickup/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-pickup/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-pickup
* <strong>Zgłoszenia błędów i propozycje funkcji</strong> - https://github.com/wppoland/plogins-pickup/issues


= Features =

* Wybór miejsca odbioru przy kasie (lista definiowana przez administratora, każde miejsce można włączyć/wyłączyć).
* Wybór daty i przedziału czasowego na podstawie tygodniowych godzin otwarcia.
* Konfigurowalna długość przedziału, pojemność na przedział, czas wyprzedzenia i horyzont rezerwacji.
* Godziny, które są zajęte lub mieszczą się w oknie czasu wyprzedzenia, są usuwane z listy.
* Wybór jest ponownie sprawdzany na serwerze przed utworzeniem zamówienia.
* Szczegóły odbioru pokazywane na ekranie zamówienia w panelu, w e-mailach z zamówieniem oraz na stronach
  zamówienia i podziękowania klienta.
* Podczas wyświetlania daty używa strefy czasowej sklepu i formatu daty WordPressa.
* Brak niestandardowych tabel i brak połączeń z usługami zewnętrznymi.
* Dostarczany z plikiem POT do tłumaczenia; podczas odinstalowania usuwa swoje ustawienia.
* Deklaruje zgodność z HPOS i działa razem z blokami Koszyka i Kasy.

== Installation ==

1. Wgraj wtyczkę do `/wp-content/plugins/pickup` lub zainstaluj przez Wtyczki → Dodaj nową.
2. Włącz ją. WooCommerce musi być aktywne.
3. Upewnij się, że w WooCommerce → Ustawienia → Wysyłka włączona jest metoda WooCommerce <strong>Odbiór osobisty</strong>.
4. Przejdź do <strong>WooCommerce → Odbiór</strong>, dodaj swoje miejsca odbioru i tygodniowe godziny otwarcia, a następnie
   ustaw długość przedziału, pojemność, czas wyprzedzenia i horyzont rezerwacji.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowane i aktywne, z metodą wysyłki Odbiór osobisty.

= When do the pickup fields show at checkout? =

Tylko wtedy, gdy wybraną przez klienta metodą wysyłki jest Odbiór osobisty WooCommerce. Przy
wszystkich innych metodach pola pozostają ukryte i nie są wymagane.

= How are time slots generated? =

Na podstawie tygodniowych godzin otwarcia i długości przedziału. Na przykład okno 09:00–12:00
przy 30-minutowej długości przedziału daje 09:00, 09:30, 10:00 i tak dalej.

= What stops a slot from being over-booked? =

Każdy przedział ma pojemność. Gdy liczba zamówień zarezerwowanych dla danego miejsca + daty
+ przedziału osiągnie tę pojemność, przedział przestaje być oferowany.

= Does it create database tables? =

Nie. Wybory są przechowywane jako metadane zamówienia, więc nie ma nic dodatkowego do utrzymania.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest zgodna z WordPress Multisite. Włącz ją w całej sieci lub na poszczególnych witrynach; każda witryna zachowuje własne ustawienia i dane.

== Screenshots ==

1. W sklepie.
2. Ustawienia w panelu WordPressa.
3. Na urządzeniu mobilnym.
== External Services ==

Pickup nie łączy się z żadnymi usługami zewnętrznymi. Sprawdzanie dostępnych przedziałów czasowych na żywo przy
kasie wysyła żądania do punktu końcowego AJAX WordPressa w Twojej własnej witrynie (`admin-ajax.php`), a
przedziały są obliczane na Twoim serwerze na podstawie skonfigurowanych przez Ciebie godzin otwarcia. Twoje
ustawienia znajdują się w opcji `pickup_settings`, a wybór dla każdego zamówienia jest zapisywany jako
metadane zamówienia (`_pickup_location`, `_pickup_date`, `_pickup_slot`); szczegóły odbioru są
pokazywane przez dodanie ich do własnych e-maili z zamówieniami WooCommerce, a nie przez wysyłanie osobnych
wiadomości. Żadne dane nie opuszczają Twojej witryny.

== Translations ==

Plogins Pickup zawiera polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki. Domena tekstowa to `plogins-pickup`, więc pakiety językowe z WordPress.org mogą też nadpisywać lub rozszerzać dołączone tłumaczenia.

== Changelog ==

= 1.0.2 =
* Dodano dołączone polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.3 =
* Zmieniono nazwę na Plogins Pickup dla WooCommerce, aby uzyskać bardziej charakterystyczną nazwę wtyczki.

= 0.1.2 =
* Filtr `pickup/slot_fee` do opcjonalnych opłat lub rabatów w koszyku dla poszczególnych przedziałów.
* Opłata koszykowa naliczana przy kasie, gdy wybrano płatny przedział; lista przedziałów AJAX zawiera kwoty opłat.

= 0.1.1 =
* Haki rozszerzeń dla Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `pickup/date_available`, `pickup/slot_available`, `pickup/blocked_dates`.
* Skrypt kasy uwzględnia zablokowane daty przekazane z serwera.

= 0.1.0 =
* Pierwsze wydanie: wybór miejsca odbioru oraz wybór daty/przedziału czasowego przy kasie,
  planowanie tygodniowych godzin otwarcia z długością przedziału, pojemnością, czasem wyprzedzenia i
  horyzontem rezerwacji, wyświetlanie w zamówieniu i e-mailu oraz ekran ustawień WooCommerce.
