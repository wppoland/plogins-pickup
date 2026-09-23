=== Prenejo - Local Pickup for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers choose a pickup location and time slot at checkout.

== Description ==

Prenejo adds click-and-collect scheduling to WooCommerce. When an order uses
WooCommerce **Local Pickup**, the customer chooses a pickup **location** and a
**date + time slot** right at checkout. The selection is validated, saved to the
order, and shown in the admin order screen and in the order emails.

Slots are generated from the weekly opening windows you define, using your chosen
slot length, minimum lead time and booking horizon. Each slot has a capacity: once
a location and time reaches that number of orders, it drops off the list so it
cannot be booked twice over.

Everything is stored as order meta, so there is no custom database table to
maintain. The checkout fields only appear when Local Pickup is selected; for
every other shipping method they stay hidden and are never required.

The picker lives on the classic checkout. With the block-based Cart and Checkout
the plugin declares compatibility and saved pickup details still show on the order,
emails and account pages, but the in-checkout field UI is the classic one.

Source code and bug reports: [github.com/wppoland/plogins-pickup](https://github.com/wppoland/plogins-pickup)

= Documentation and links =

* **Documentation**: [plogins.com/plogins-pickup/docs/](https://plogins.com/plogins-pickup/docs/)
* **Plugin page**: [plogins.com/plogins-pickup/](https://plogins.com/plogins-pickup/)
* **Source code**: [github.com/wppoland/plogins-pickup](https://github.com/wppoland/plogins-pickup)
* **Bug reports and feature requests**: [github.com/wppoland/plogins-pickup/issues](https://github.com/wppoland/plogins-pickup/issues)


= Features =

* Pickup location chooser at checkout (admin-defined list, enable/disable each).
* Date + time-slot picker driven by your weekly opening hours.
* Configurable slot length, per-slot capacity, lead time and booking horizon.
* Times that are full or inside the lead-time window are dropped from the list.
* The selection is checked again on the server before the order is created.
* Pickup details shown on the admin order screen, in order emails, and on the
  customer's order and thank-you pages.
* Uses your store timezone and WordPress date format when showing the date.
* No custom tables and no calls to outside services.
* Ships with a POT file for translation and removes its settings on uninstall.
* Declares HPOS compatibility and works alongside the Cart and Checkout blocks.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/prenejo`, or install via Plugins > Add New.
2. Activate it. WooCommerce must be active.
3. Make sure WooCommerce **Local Pickup** is enabled under WooCommerce > Settings > Shipping.
4. Go to **WooCommerce > Pickup**, add your locations and weekly opening hours, and
   set the slot length, capacity, lead time and booking horizon.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active, with a Local Pickup shipping method.

= When do the pickup fields show at checkout? =

Only when the customer's chosen shipping method is WooCommerce Local Pickup. For
all other methods the fields stay hidden and are not required.

= How are time slots generated? =

From your weekly opening windows and the slot length. For example, a 09:00-12:00
window with a 30-minute slot length offers 09:00, 09:30, 10:00 and so on.

= What stops a slot from being over-booked? =

Each slot has a capacity. Once the number of orders booked into a location + date
+ slot reaches that capacity, the slot is no longer offered.

= Does it create database tables? =

No. Selections are stored as order meta, so there is nothing extra to maintain.


= Does this plugin work on WordPress Multisite? =

Yes. This plugin is compatible with WordPress Multisite. Network activate it or activate it on individual sites; each site keeps its own settings and data.

== Screenshots ==

1. On the storefront.
2. Settings in the WordPress admin.
3. On a mobile device.
== External Services ==

Pickup does not connect to any external services. The live time-slot lookup at
checkout posts to your own site's WordPress AJAX endpoint (`admin-ajax.php`) and
the slots are calculated on your server from the opening hours you configure. Your
settings live in the `pickup_settings` option and each order's choice is stored as
order meta (`_pickup_location`, `_pickup_date`, `_pickup_slot`); pickup details are
shown by adding them to WooCommerce's own order emails, not by sending any mail of
their own. No data leaves your site.

== Translations ==

Prenejo is fully translatable and ships the `prenejo.pot` template. Translations are delivered by WordPress.org language packs from translate.wordpress.org, which is where Polish, German and Spanish are being contributed; the package itself carries no compiled translation files.

== Changelog ==

= 1.1.1 =
* The sidebar upgrade promo now follows the same dismissal as the banner. Dismissing the banner used to leave a full-height advert on the settings screen for good, which is not what the WordPress.org guideline on upgrade prompts means by used with moderation.

= 1.1.0 =
* Renamed to Prenejo. The WordPress.org review team asks a plugin name to lead with a distinctive, coined identifier rather than a generic descriptive word. Prenejo is Esperanto for a place to collect from. The text domain follows the name; the stored data, the settings and every hook are unchanged.

= 1.0.16 =
* Fixed: the pickup location dropdown at checkout joined the location name to its address with no spaces, so a shop with an address on file offered "Main store-12 High Street". The two are now separated by a comma and a space. The July punctuation sweep is where the spaces went: it replaced the whole separator rather than only the long dash inside it.
* Fixed: deleting the plugin from a WordPress network removed the pickup settings from one site only. Uninstall runs once, on whichever site the deletion was started from, so every other site in the network kept its row of locations, opening hours and capacity in the database for good. Uninstall now walks every site in the network.

= 1.0.15 =
* Fixed: the other half of the network defect 1.0.14 started on. The bookings were counted per site, but the settings each site's slots are built from (capacity, opening hours, locations) were read once per request and then reused after a switch to another site. A request that served two sites could check the second site's bookings against the first site's capacity, so a slot could take more bookings than that site allows. Settings are now read per site as well.

= 1.0.14 =
* Fixed: the booking counts added in 1.0.13 were kept per pickup location only. On a WordPress network, a request that serves more than one site could answer one site's availability with another site's bookings. The counts are now kept per site as well.
* The booking count asks WooCommerce which order types to count again, the way the lookup did before 1.0.13, instead of counting plain orders only. A stock shop sees no difference, because a refund never carries a pickup time; a shop whose extension registers its own order type has those bookings counted again.

= 1.0.13 =
* Fixed: picking a pickup time made the cart and the checkout slow, and slower the more orders the shop had. The list of bookable times asked the database once per time slot, so a single page load ran one query for every time slot it checked, the ones it then dropped as already full included, and the whole list was rebuilt every time WooCommerce recalculated the cart totals, not only when the shopper changed the date. It now comes from one grouped query per pickup location, held for the rest of the request. The same times are offered, with the same capacity and the same order statuses counted.
* Capacity no longer counts bookings on dates that have already passed. Those dates were never offered for booking, so nothing a shopper can select changes.

= 1.0.12 =
* Fixed: the PRO upgrade promo kept selling to people who had already bought the paid edition. Only the banner could be dismissed, so the sidebar promo and the locked feature cards followed a paying customer around for good. The promo now checks whether the paid edition is active and steps aside when it is.
* Fixed: arrow glyphs in the admin menu paths, and in the strings handed to translators. An arrow inside a translatable string makes the glyph every translator's problem and changes the layout in any locale that drops it.

= 1.0.11 =
* Fixed: the pickup location a fresh install starts with was named "Main store" in English in every language, and that name is shown to shoppers at checkout. The default is now translated. A location you have renamed yourself is untouched.

= 1.0.10 =
* Fixed: deleting the plugin left the per-user "dismiss" flag from the PRO notice in the database. Uninstall now removes it for every user, not just the one who dismissed it.

= 1.0.9 =
* The translation template was regenerated. It still named an older version of the plugin and pointed at source lines that had since moved, which is what translation tools read to show a string in context.

= 1.0.8 =
* Renamed to Plogins Pickup - Local Pickup for WooCommerce so the name leads with the brand rather than a generic word, which is what the WordPress.org plugin review team asks for. The plugin slug is unchanged.

= 1.0.7 =
* Tested against WordPress 7.1. Verified by activating this build on a clean 7.1 install with WooCommerce 11.1, not by editing the header.

= 1.0.6 =
* Fixed the PRO promo on the settings screen quoting a price in PLN. PRO is priced and charged in EUR, so an admin on a Polish site was shown a zloty amount and then billed in euro, and the zloty figure was a fixed conversion that drifted from the real charge as the rate moved. The promo now shows the euro price that is actually taken.

= 1.0.4 =
* Translations: completed Polish, German and Spanish for the PRO upgrade panel.

= 1.0.3 =
* Accessibility improvements to the admin and storefront markup.
* Fixed low-contrast admin headings under an OS dark-mode preference.

= 1.0.2 =
* Added bundled Polish, German and Spanish translations for the plugin interface.

= 1.0.1 =
* First stable release.

= 0.1.3 =
* Renamed to Plogins Pickup for WooCommerce for a more distinctive plugin name.

= 0.1.2 =
* `pickup/slot_fee` filter for optional per-slot cart fees or discounts.
* Cart fee applied at checkout when a priced slot is selected; AJAX slot list includes fee amounts.

= 0.1.1 =
* Extension hooks for Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `pickup/date_available`, `pickup/slot_available`, `pickup/blocked_dates`.
* Checkout script honours blocked dates passed from the server.

= 0.1.0 =
* Initial release: pickup location chooser and date/time-slot picker at checkout,
  weekly opening-hours scheduling with slot length, capacity, lead time and
  booking horizon, order + email display, and a WooCommerce settings screen.
