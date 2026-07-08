=== Plogins Pickup - Local Pickup for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, local pickup, click and collect, scheduling, checkout
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requiere complementos: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permita que los clientes elijan un lugar de recogida y un horario al finalizar la compra.

== Description ==

La recogida añade programación de hacer clic y recoger a WooCommerce. Cuando una orden utiliza
WooCommerce <strong>Recogida local</strong>, el cliente elige una <strong>ubicación</strong> de recogida y un
<strong>fecha + franja horaria</strong> justo al finalizar la compra. La selección se valida, se guarda en el
pedido, y se muestra en la pantalla de pedido del administrador y en los correos electrónicos del pedido.

Los espacios se generan a partir de las ventanas de apertura semanales que defines, utilizando tu elección
duración del espacio, plazo mínimo de entrega y horizonte de reserva. Cada ranura tiene una capacidad: una vez
una ubicación y hora alcanza esa cantidad de pedidos, sale de la lista para que
No se puede reservar dos veces.

Todo se almacena como meta de pedido, por lo que no hay una tabla de base de datos personalizada para
mantener. Los campos de pago solo aparecen cuando se selecciona Recogida local; para
Con cualquier otro método de envío, permanecen ocultos y nunca son necesarios.

El recolector vive de la caja clásica. Con el carrito y el pago basados en bloques
el complemento declara compatibilidad y los detalles de recogida guardados aún se muestran en el pedido,
correos electrónicos y páginas de cuentas, pero la interfaz de usuario del campo de pago es la clásica.

Código fuente e informes de errores: https://github.com/wppoland/plogins-pickup

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-pickup/docs/
* <strong>Página de complementos</strong> - https://plogins.com/es/plogins-pickup/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-pickup
* <strong>Informes de errores y solicitudes de funciones</strong> - https://github.com/wppoland/plogins-pickup/issues


= Features =

* Selector de ubicación de recogida al finalizar la compra (lista definida por el administrador, habilitar/deshabilitar cada una).
* Selector de fecha y franja horaria según su horario de apertura semanal.
* Longitud de ranura configurable, capacidad por ranura, plazo de entrega y horizonte de reserva.
* Los tiempos que están llenos o dentro del plazo de entrega se eliminan de la lista.
* La selección se verifica nuevamente en el servidor antes de crear el pedido.
* Los detalles de recogida se muestran en la pantalla de pedidos del administrador, en los correos electrónicos del pedido y en el
  pedidos del cliente y páginas de agradecimiento.
* Utiliza la zona horaria de su tienda y el formato de fecha de WordPress al mostrar la fecha.
* Sin mesas personalizadas ni llamadas a servicios externos.
* Se envía con un archivo POT para traducir y elimina tu configuración al desinstalarlo.
* Declara compatibilidad con HPOS y funciona junto con los bloques Carrito y Pago.

== Installation ==

1. Cargue el complemento en `/wp-content/plugins/pickup`, o instálelo a través de Complementos → Añadir nuevo.
2. Actívalo. WooCommerce debe estar activo.
3. Asegúrese de que la <strong>Recogida local<strong> de WooCommerce esté habilitada en WooCommerce → Configuración → Envío. 4. Vaya a </strong>WooCommerce → Recogida</strong>, añade sus ubicaciones y horarios de apertura semanales, y
   establezca la duración del espacio, la capacidad, el plazo de entrega y el horizonte de reserva.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo, con un método de envío con recogida local.

= When do the pickup fields show at checkout? =

Solo cuando el método de envío elegido por el cliente es Recolección local de WooCommerce. Para
En todos los demás métodos, los campos permanecen ocultos y no son obligatorios.

= How are time slots generated? =

Desde sus ventanas de apertura semanales y la duración del espacio. Por ejemplo, de 09:00 a 12:00
La ventana con una duración de 30 minutos ofrece 09:00, 09:30, 10:00, etc.

= What stops a slot from being over-booked? =

Cada ranura tiene una capacidad. Una vez que el número de pedidos reservados en una ubicación + fecha
+ el slot alcanza esa capacidad, el slot ya no se ofrece.

= Does it create database tables? =

No. Las selecciones se almacenan como meta del pedido, por lo que no hay nada adicional que mantener.


= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. En el escaparate.
2. Configuración en el administrador de WordPress.
3. En un dispositivo móvil.
== External Services ==

La recogida no se conecta a ningún servicio externo. La búsqueda de franjas horarias en vivo en
pagar publicaciones en el punto final AJAX de WordPress de tu propio sitio (`admin-ajax.php`) y
los slots se calculan en tu servidor a partir del horario de apertura que configures. tu
La configuración se encuentra en la opción `pickup_settings` y la elección de cada pedido se almacena como
meta del pedido (`_pickup_location`, `_pickup_date`, `_pickup_slot`); los detalles de recogida son
se muestra agregándolos a los correos electrónicos de pedidos de WooCommerce, no enviando ningún correo de
los suyos propios. Ningún dato sale de tu sitio.

== Changelog ==

= 1.0.1 =
* Primera versión estable.

= 0.1.3 =
* Se cambió el nombre a Plogins Pickup para WooCommerce para obtener un nombre de complemento más distintivo.

= 0.1.2 =
* Filtro `pickup/slot_fee` para tarifas o descuentos opcionales por carrito por espacio.
* La tarifa del carrito se aplica al finalizar la compra cuando se selecciona un espacio con precio; La lista de espacios AJAX incluye montos de tarifas.

= 0.1.1 =
* Ganchos de extensión para Pickup Pro: `pickup/booted`, `pickup/slot_capacity`,
  `recogida/fecha_disponible`, `recogida/espacio_disponible`, `recogida/fechas_bloqueadas`.
* El script de pago respeta las fechas bloqueadas transmitidas por el servidor.

= 0.1.0 =
* Lanzamiento inicial: selector de lugar de recogida y selector de fecha/intervalo horario al finalizar la compra,
  Programación semanal de horarios de apertura con duración de franjas horarias, capacidad, plazos de entrega y
  horizonte de reserva, visualización de pedido + correo electrónico y una pantalla de configuración de WooCommerce.
