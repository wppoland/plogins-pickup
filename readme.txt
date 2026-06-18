=== Pickup - Local Pickup Scheduling for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers choose a pickup location and time slot at checkout.

== Description ==

Pickup adds click-and-collect scheduling to WooCommerce. When an order uses
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

Source code and bug reports: https://github.com/wppoland/pickup

= Documentation and links =

* **Documentation** - https://plogins.com/pickup/docs/
* **Plugin page** - https://plogins.com/pickup/
* **Source code** - https://github.com/wppoland/pickup
* **Bug reports and feature requests** - https://github.com/wppoland/pickup/issues
* **Discussions and questions** - https://github.com/wppoland/pickup/discussions


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

1. Upload the plugin to `/wp-content/plugins/pickup`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Make sure WooCommerce **Local Pickup** is enabled under WooCommerce → Settings → Shipping.
4. Go to **WooCommerce → Pickup**, add your locations and weekly opening hours, and
   set the slot length, capacity, lead time and booking horizon.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active, with a Local Pickup shipping method.

= When do the pickup fields show at checkout? =

Only when the customer's chosen shipping method is WooCommerce Local Pickup. For
all other methods the fields stay hidden and are not required.

= How are time slots generated? =

From your weekly opening windows and the slot length. For example, a 09:00–12:00
window with a 30-minute slot length offers 09:00, 09:30, 10:00 and so on.

= What stops a slot from being over-booked? =

Each slot has a capacity. Once the number of orders booked into a location + date
+ slot reaches that capacity, the slot is no longer offered.

= Does it create database tables? =

No. Selections are stored as order meta, so there is nothing extra to maintain.

== Screenshots ==

1. The pickup location and time-slot fields at checkout.

== External Services ==

Pickup does not connect to any external services. The live time-slot lookup at
checkout posts to your own site's WordPress AJAX endpoint (`admin-ajax.php`) and
the slots are calculated on your server from the opening hours you configure. Your
settings live in the `pickup_settings` option and each order's choice is stored as
order meta (`_pickup_location`, `_pickup_date`, `_pickup_slot`); pickup details are
shown by adding them to WooCommerce's own order emails, not by sending any mail of
their own. No data leaves your site.

== Changelog ==

= 0.1.1 =
* Extension hooks for Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `pickup/date_available`, `pickup/slot_available`, `pickup/blocked_dates`.
* Checkout script honours blocked dates passed from the server.

= 0.1.0 =
* Initial release: pickup location chooser and date/time-slot picker at checkout,
  weekly opening-hours scheduling with slot length, capacity, lead time and
  booking horizon, order + email display, and a WooCommerce settings screen.
