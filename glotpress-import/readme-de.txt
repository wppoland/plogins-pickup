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

Lass deine Kundschaft an der Kasse einen Abholort und ein Zeitfenster auswählen.

== Description ==

Pickup fügt WooCommerce die Click-and-Collect-Planung hinzu. Wenn eine Bestellung die
WooCommerce-Methode <strong>Abholung vor Ort</strong> nutzt, wählt die Kundschaft direkt an der Kasse einen
<strong>Abholort</strong> sowie ein <strong>Datum + Zeitfenster</strong>. Die Auswahl wird validiert, in der
Bestellung gespeichert und im Bestellbildschirm im Adminbereich sowie in den Bestell-E-Mails angezeigt.

Zeitfenster werden aus den wöchentlichen Öffnungszeiten erzeugt, die du festlegst, unter Verwendung deiner
gewählten Zeitfensterlänge, Mindestvorlaufzeit und deines Buchungshorizonts. Jedes Zeitfenster hat eine
Kapazität: sobald ein Ort und eine Zeit diese Anzahl an Bestellungen erreichen, fallen sie von der Liste, sodass
sie nicht doppelt gebucht werden können.

Alles wird als Bestell-Metadaten gespeichert, sodass keine eigene Datenbanktabelle zu pflegen ist. Die
Kassenfelder erscheinen nur, wenn „Abholung vor Ort“ gewählt ist; bei jeder anderen
Versandart bleiben sie ausgeblendet und werden nie verlangt.

Das Auswahlfeld lebt auf der klassischen Kasse. Beim block-basierten Warenkorb und der block-basierten Kasse
erklärt das Plugin die Kompatibilität, und gespeicherte Abholdetails erscheinen weiterhin in Bestellung,
E-Mails und auf den Kontoseiten, doch die Feld-Oberfläche in der Kasse ist die klassische.

Quellcode und Fehlerberichte: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-pickup/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-pickup/
* <strong>Quellcode</strong> - https://github.com/wppoland/plogins-pickup
* <strong>Fehlerberichte und Funktionswünsche</strong> - https://github.com/wppoland/plogins-pickup/issues


= Features =

* Auswahl des Abholorts an der Kasse (vom Admin definierte Liste, jeder Eintrag einzeln aktivierbar/deaktivierbar).
* Datums- und Zeitfensterauswahl, gesteuert durch deine wöchentlichen Öffnungszeiten.
* Konfigurierbare Zeitfensterlänge, Kapazität pro Zeitfenster, Vorlaufzeit und Buchungshorizont.
* Zeiten, die voll sind oder innerhalb der Vorlaufzeit liegen, werden aus der Liste entfernt.
* Die Auswahl wird vor dem Anlegen der Bestellung erneut auf dem Server geprüft.
* Abholdetails werden im Bestellbildschirm im Adminbereich, in Bestell-E-Mails sowie auf den
  Bestell- und Danke-Seiten der Kundschaft angezeigt.
* Verwendet für die Datumsanzeige die Zeitzone deines Shops und das WordPress-Datumsformat.
* Keine eigenen Tabellen und keine Aufrufe externer Dienste.
* Wird mit einer POT-Datei zur Übersetzung geliefert und entfernt seine Einstellungen bei der Deinstallation.
* Erklärt HPOS-Kompatibilität und funktioniert neben den Warenkorb- und Kassen-Blöcken.

== Installation ==

1. Lade das Plugin nach `/wp-content/plugins/pickup` hoch oder installiere es über Plugins → Installieren.
2. Aktiviere es. WooCommerce muss aktiv sein.
3. Stelle sicher, dass die WooCommerce-Methode <strong>Abholung vor Ort</strong> unter WooCommerce → Einstellungen → Versand aktiviert ist.
4. Gehe zu <strong>WooCommerce → Abholung</strong>, füge deine Abholorte und wöchentlichen Öffnungszeiten hinzu und
   lege Zeitfensterlänge, Kapazität, Vorlaufzeit und Buchungshorizont fest.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein, mit einer Versandart „Abholung vor Ort“.

= When do the pickup fields show at checkout? =

Nur wenn die von der Kundschaft gewählte Versandart „Abholung vor Ort“ von WooCommerce ist. Bei
allen anderen Methoden bleiben die Felder ausgeblendet und werden nicht verlangt.

= How are time slots generated? =

Aus deinen wöchentlichen Öffnungszeiten und der Zeitfensterlänge. Ein Fenster von 09:00–12:00 Uhr
mit einer Zeitfensterlänge von 30 Minuten bietet zum Beispiel 09:00, 09:30, 10:00 und so weiter.

= What stops a slot from being over-booked? =

Jedes Zeitfenster hat eine Kapazität. Sobald die Anzahl der für einen Ort + ein Datum + ein Zeitfenster
gebuchten Bestellungen diese Kapazität erreicht, wird das Zeitfenster nicht mehr angeboten.

= Does it create database tables? =

Nein. Die Auswahl wird als Bestell-Metadaten gespeichert, sodass nichts zusätzlich zu pflegen ist.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es netzwerkweit oder auf einzelnen Websites; jede Website behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Im Shop.
2. Einstellungen im WordPress-Adminbereich.
3. Auf einem Mobilgerät.
== External Services ==

Pickup stellt keine Verbindung zu externen Diensten her. Die Live-Abfrage der Zeitfenster an der
Kasse sendet Anfragen an den WordPress-AJAX-Endpunkt deiner eigenen Website (`admin-ajax.php`), und
die Zeitfenster werden auf deinem Server aus den von dir konfigurierten Öffnungszeiten berechnet. Deine
Einstellungen liegen in der Option `pickup_settings`, und die Auswahl jeder Bestellung wird als
Bestell-Metadaten gespeichert (`_pickup_location`, `_pickup_date`, `_pickup_slot`); Abholdetails werden
angezeigt, indem sie zu den eigenen Bestell-E-Mails von WooCommerce hinzugefügt werden, nicht durch das
Versenden eigener E-Mails. Keine Daten verlassen deine Website.

== Translations ==

Plogins Pickup enthält deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche. Die Textdomain ist `plogins-pickup`, sodass Sprachpakete von WordPress.org diese mitgelieferten Übersetzungen ebenfalls überschreiben oder erweitern können.

== Changelog ==

= 1.0.2 =
* Deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche mitgeliefert.

= 1.0.1 =
* Erste stabile Version.

= 0.1.3 =
* In Plogins Pickup für WooCommerce umbenannt, für einen unverwechselbareren Plugin-Namen.

= 0.1.2 =
* Filter `pickup/slot_fee` für optionale Warenkorb-Gebühren oder -Rabatte pro Zeitfenster.
* Warenkorb-Gebühr an der Kasse, wenn ein bepreistes Zeitfenster gewählt wird; die AJAX-Zeitfensterliste enthält die Gebührenbeträge.

= 0.1.1 =
* Erweiterungs-Hooks für Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `pickup/date_available`, `pickup/slot_available`, `pickup/blocked_dates`.
* Das Kassen-Skript berücksichtigt vom Server übergebene gesperrte Termine.

= 0.1.0 =
* Erste Veröffentlichung: Auswahl des Abholorts und Datums-/Zeitfensterauswahl an der Kasse,
  Planung wöchentlicher Öffnungszeiten mit Zeitfensterlänge, Kapazität, Vorlaufzeit und
  Buchungshorizont, Anzeige in Bestellung + E-Mail sowie ein WooCommerce-Einstellungsbildschirm.
