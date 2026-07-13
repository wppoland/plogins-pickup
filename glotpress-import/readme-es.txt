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

Permite que tus clientes elijan un lugar de recogida y una franja horaria al finalizar la compra.

== Description ==

Pickup añade a WooCommerce la programación de «click and collect». Cuando un pedido usa la
<strong>Recogida local</strong> de WooCommerce, el cliente elige un <strong>lugar</strong> de recogida y una
<strong>fecha + franja horaria</strong> justo al finalizar la compra. La selección se valida, se guarda en el
pedido y se muestra en la pantalla de pedidos de la administración y en los correos del pedido.

Las franjas se generan a partir de las ventanas de apertura semanales que defines, usando la
duración de franja, la antelación mínima y el horizonte de reserva que elijas. Cada franja tiene una
capacidad: cuando un lugar y una hora alcanzan ese número de pedidos, desaparecen de la lista, así que
no se pueden reservar dos veces.

Todo se guarda como metadatos del pedido, así que no hay ninguna tabla de base de datos propia que
mantener. Los campos de la página de pago solo aparecen cuando se elige la Recogida local; con
cualquier otro método de envío permanecen ocultos y nunca son obligatorios.

El selector vive en la página de pago clásica. Con el Carrito y el Pago basados en bloques el plugin
declara compatibilidad y los datos de recogida guardados siguen mostrándose en el pedido, los correos
y las páginas de la cuenta, pero la interfaz del campo dentro del pago es la clásica.

Código fuente e informes de errores: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-pickup/docs/
* <strong>Página del plugin</strong> - https://plogins.com/es/plogins-pickup/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-pickup
* <strong>Informes de errores y peticiones de funciones</strong> - https://github.com/wppoland/plogins-pickup/issues


= Features =

* Selector del lugar de recogida al finalizar la compra (lista definida por la administración, cada uno activable o desactivable).
* Selector de fecha y franja horaria basado en tus horarios de apertura semanales.
* Duración de franja, capacidad por franja, antelación y horizonte de reserva configurables.
* Las horas que están completas o dentro de la ventana de antelación se quitan de la lista.
* La selección se comprueba de nuevo en el servidor antes de crear el pedido.
* Los datos de recogida se muestran en la pantalla de pedidos de la administración, en los correos del pedido y en las
  páginas de pedido y de agradecimiento del cliente.
* Usa la zona horaria de tu tienda y el formato de fecha de WordPress al mostrar la fecha.
* Sin tablas propias y sin llamadas a servicios externos.
* Se distribuye con un archivo POT para la traducción y elimina sus ajustes al desinstalarlo.
* Declara compatibilidad con HPOS y funciona junto a los bloques de Carrito y Pago.

== Installation ==

1. Sube el plugin a `/wp-content/plugins/pickup` o instálalo desde Plugins → Añadir nuevo.
2. Actívalo. WooCommerce debe estar activo.
3. Asegúrate de que la <strong>Recogida local</strong> de WooCommerce esté activada en WooCommerce → Ajustes → Envío.
4. Ve a <strong>WooCommerce → Recogida</strong>, añade tus lugares y tus horarios de apertura semanales, y
   define la duración de franja, la capacidad, la antelación y el horizonte de reserva.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo, con un método de envío de Recogida local.

= When do the pickup fields show at checkout? =

Solo cuando el método de envío elegido por el cliente es la Recogida local de WooCommerce. Con
todos los demás métodos, los campos permanecen ocultos y no son obligatorios.

= How are time slots generated? =

A partir de tus ventanas de apertura semanales y la duración de franja. Por ejemplo, una ventana de 09:00–12:00
con una duración de franja de 30 minutos ofrece 09:00, 09:30, 10:00, etc.

= What stops a slot from being over-booked? =

Cada franja tiene una capacidad. Cuando el número de pedidos reservados en un lugar + fecha
+ franja alcanza esa capacidad, la franja deja de ofrecerse.

= Does it create database tables? =

No. Las selecciones se guardan como metadatos del pedido, así que no hay nada extra que mantener.


= Does this plugin work on WordPress Multisite? =

Sí. Este plugin es compatible con WordPress Multisite. Actívalo para toda la red o en sitios individuales; cada sitio conserva sus propios ajustes y datos.

== Screenshots ==

1. En la tienda.
2. Ajustes en la administración de WordPress.
3. En un dispositivo móvil.
== External Services ==

Pickup no se conecta a ningún servicio externo. La consulta en directo de franjas horarias al finalizar la
compra envía peticiones al endpoint AJAX de WordPress de tu propio sitio (`admin-ajax.php`), y
las franjas se calculan en tu servidor a partir de los horarios de apertura que configures. Tus
ajustes viven en la opción `pickup_settings` y la elección de cada pedido se guarda como
metadatos del pedido (`_pickup_location`, `_pickup_date`, `_pickup_slot`); los datos de recogida se
muestran añadiéndolos a los propios correos de pedido de WooCommerce, no enviando ningún correo
propio. Ningún dato sale de tu sitio.

== Translations ==

Plogins Pickup incluye traducciones al polaco, al alemán y al español para la interfaz del plugin. El dominio de texto es `plogins-pickup`, por lo que los paquetes de idioma de WordPress.org también pueden sustituir o ampliar estas traducciones incluidas.

== Changelog ==

= 1.0.2 =
* Añadidas traducciones al polaco, al alemán y al español para la interfaz del plugin.

= 1.0.1 =
* Primera versión estable.

= 0.1.3 =
* Renombrado a Plogins Pickup para WooCommerce para lograr un nombre de plugin más distintivo.

= 0.1.2 =
* Filtro `pickup/slot_fee` para tarifas o descuentos opcionales por franja en el carrito.
* Tarifa de carrito aplicada al finalizar la compra cuando se elige una franja con precio; la lista de franjas AJAX incluye los importes de las tarifas.

= 0.1.1 =
* Hooks de extensión para Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `pickup/date_available`, `pickup/slot_available`, `pickup/blocked_dates`.
* El script de pago respeta las fechas bloqueadas enviadas desde el servidor.

= 0.1.0 =
* Versión inicial: selector del lugar de recogida y selector de fecha/franja horaria al finalizar la compra,
  programación semanal de horarios de apertura con duración de franja, capacidad, antelación y
  horizonte de reserva, visualización en pedido + correo y una pantalla de ajustes de WooCommerce.
