=== Plogins Pickup - Local Pickup for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Erfordert Plugins: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lass Kunden an der Kasse einen Abholort und ein Zeitfenster auswählen.

== Description ==

Pickup fügt WooCommerce die Click-and-Collect-Planung hinzu. Wenn eine Bestellung verwendet wird
WooCommerce <strong>Lokale Abholung</strong>, der Kunde wählt einen <strong>Abholort</strong> und a
<strong>Datum + Zeitfenster</strong> direkt an der Kasse. Die Auswahl wird validiert und im gespeichert
Bestellung und wird im Admin-Bestellbildschirm und in den Bestell-E-Mails angezeigt.

Slots werden aus den von dir definierten wöchentlichen Öffnungsfenstern anhand deiner Auswahl generiert
Slotlänge, Mindestvorlaufzeit und Buchungshorizont. Jeder Steckplatz hat eine Kapazität: einmal
Wenn ein Ort und eine bestimmte Zeit diese Anzahl an Bestellungen erreicht, wird er von der Liste gestrichen
Eine Doppelbuchung ist nicht möglich.

Alles wird als Auftragsmeta gespeichert, sodass keine benutzerdefinierte Datenbanktabelle erforderlich ist
pflegen. Die Checkout-Felder werden nur angezeigt, wenn „Abholung vor Ort“ ausgewählt ist. für
Bei jeder anderen Versandart bleiben sie verborgen und werden nie benötigt.

Der Kommissionierer lebt von der klassischen Kasse. Mit dem blockbasierten Warenkorb und Checkout
Das Plugin erklärt die Kompatibilität und gespeicherte Abholdetails werden weiterhin auf der Bestellung angezeigt.
E-Mails und Kontoseiten, aber die Benutzeroberfläche des In-Checkout-Felds ist die klassische.

Quellcode und Fehlerberichte: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-pickup/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-pickup/
* <strong>Quellcode</strong> – https://github.com/wppoland/plogins-pickup
* <strong>Fehlerberichte und Funktionsanfragen</strong> – https://github.com/wppoland/plogins-pickup/issues


= Features =

* Auswahl des Abholorts an der Kasse (vom Administrator definierte Liste, jeweils aktivieren/deaktivieren).
* Datums- und Zeitfensterauswahl basierend auf deinen wöchentlichen Öffnungszeiten.
* Konfigurierbare Slot-Länge, Kapazität pro Slot, Vorlaufzeit und Buchungshorizont.
* Zeiten, die voll sind oder innerhalb des Durchlaufzeitfensters liegen, werden aus der Liste gelöscht.
* Die Auswahl wird vor der Erstellung der Bestellung noch einmal auf dem Server überprüft.
* Abholdetails werden auf dem Admin-Bestellbildschirm, in Bestell-E-Mails und auf der angezeigt
  Bestell- und Dankesseiten des Kunden.
* Verwendet die Zeitzone Ihres Shops und das WordPress-Datumsformat, wenn das Datum angezeigt wird.
* Keine benutzerdefinierten Tabellen und keine Anrufe bei externen Diensten.
* Wird mit einer POT-Datei zur Übersetzung geliefert und entfernt deren Einstellungen bei der Deinstallation.
* Erklärt HPOS-Kompatibilität und funktioniert neben den Warenkorb- und Checkout-Blöcken.

== Installation ==

1. Lade das Plugin nach „/wp-content/plugins/pickup“ hoch oder installiere es über Plugins → Neu hinzufügen.
2. Aktiviere es. WooCommerce muss aktiv sein.
3. Stelle sicher, dass WooCommerce <strong>Lokale Abholung<strong> unter WooCommerce → Einstellungen → Versand aktiviert ist. 4. Gehe zu </strong>WooCommerce → Abholung</strong>, füge deine Standorte und wöchentlichen Öffnungszeiten hinzu und
   Lege Slotlänge, Kapazität, Vorlaufzeit und Buchungshorizont fest.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein, mit der Versandart „Lokale Abholung“.

= When do the pickup fields show at checkout? =

Nur wenn die vom Kunden gewählte Versandart WooCommerce Local Pickup ist. Für
Bei allen anderen Methoden bleiben die Felder verborgen und sind nicht erforderlich.

= How are time slots generated? =

Von deinen wöchentlichen Öffnungsfenstern und der Slotlänge. Beispiel: 09:00–12:00 Uhr
Fenster mit einer Slotlänge von 30 Minuten bieten 09:00, 09:30, 10:00 und so weiter.

= What stops a slot from being over-booked? =

Jeder Steckplatz hat eine Kapazität. Einmal die Anzahl der an einem Standort gebuchten Bestellungen + Datum
+ Steckplatz diese Kapazität erreicht, wird der Steckplatz nicht mehr angeboten.

= Does it create database tables? =

Nein. Die Auswahl wird als Auftragsmeta gespeichert, sodass nichts extra gepflegt werden muss.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Im Schaufenster.
2. Einstellungen im WordPress-Admin.
3. Auf einem mobilen Gerät.
== External Services ==

Die Abholung stellt keine Verbindung zu externen Diensten her. Die Live-Zeitfenstersuche unter
Checkout-Beiträge an den WordPress-AJAX-Endpunkt deiner eigenen Website („admin-ajax.php“) und
Die Slots werden auf deinem Server anhand der von dir konfigurierten Öffnungszeiten berechnet. dein
Die Einstellungen befinden sich in der Option „pickup_settings“ und die Auswahl jeder Bestellung wird als gespeichert
Bestellmeta (`_pickup_location`, `_pickup_date`, `_pickup_slot`); Abholdetails sind
angezeigt, indem du sie zu den eigenen Bestell-E-Mails von WooCommerce hinzufügen, nicht durch das Versenden von E-Mails von
ihre eigenen. Keine Daten verlassen deine Website.

== Changelog ==

= 1.0.1 =
* Erste stabile Version.

= 0.1.3 =
* Für einen eindeutigeren Plugin-Namen in „Plogins Pickup for WooCommerce“ umbenannt.

= 0.1.2 =
* „Pickup/Slot_fee“-Filter für optionale Gebühren oder Rabatte pro Slot im Warenkorb.
* Beim Bezahlvorgang wird eine Warenkorbgebühr erhoben, wenn ein kostenpflichtiger Slot ausgewählt wird. Die AJAX-Slot-Liste enthält Gebührenbeträge.

= 0.1.1 =
* Erweiterungshaken für Pickup Pro: „pickup/booted“, „pickup/slot_capacity“,
  „Abholung/Verfügbarkeitsdatum“, „Verfügbarkeit/Slot_Verfügbarkeit“, „Abholung/Gesperrte_Daten“.
* Das Checkout-Skript berücksichtigt vom Server übergebene blockierte Daten.

= 0.1.0 =
* Erstveröffentlichung: Auswahl des Abholorts und Auswahl des Datums/Zeitfensters an der Kasse,
  wöchentliche Öffnungszeitenplanung mit Zeitfensterlänge, Kapazität, Durchlaufzeit usw
  Buchungshorizont, Bestell- und E-Mail-Anzeige und ein WooCommerce-Einstellungsbildschirm.
