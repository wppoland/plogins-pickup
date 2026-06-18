/**
 * Pickup — checkout enhancements (progressive, dependency-free).
 *
 * - Shows the pickup fields only when WooCommerce Local Pickup is the active
 *   shipping method (the server still validates regardless of JS).
 * - Loads the available time slots for the chosen location + date via AJAX so
 *   the time dropdown always reflects live capacity and lead time.
 *
 * Loaded with `defer`. With JS off, the fields are visible and validated server
 * side; the time dropdown is pre-rendered from any posted-back selection.
 */
( function () {
	'use strict';

	var cfg = window.PickupCheckout || {};
	var i18n = cfg.i18n || {};

	function getFields() {
		return document.querySelector( '[data-pickup-fields]' );
	}

	/* ---- Toggle visibility with the chosen shipping method ----------- */

	function isLocalPickupSelected() {
		var inputs = document.querySelectorAll(
			'input[name^="shipping_method"]'
		);
		for ( var i = 0; i < inputs.length; i++ ) {
			var input = inputs[ i ];
			var checkedRadio = input.type === 'radio' ? input.checked : true;
			if ( checkedRadio && /^(local_pickup|pickup_location)/.test( input.value ) ) {
				return true;
			}
		}
		return false;
	}

	function syncVisibility() {
		var fields = getFields();
		if ( ! fields ) {
			return;
		}
		fields.hidden = ! isLocalPickupSelected();
	}

	/* ---- The crafted moment: stamp the claim stub on slot select ----- */

	function syncReserved() {
		var fields = getFields();
		if ( ! fields ) {
			return;
		}
		var slotEl = fields.querySelector( '[data-pickup-slot]' );
		var reserved = !! ( slotEl && slotEl.value );
		// Re-trigger the stamp animation only on a genuine transition.
		if ( reserved && ! fields.classList.contains( 'is-reserved' ) ) {
			fields.classList.add( 'is-reserved' );
		} else if ( ! reserved ) {
			fields.classList.remove( 'is-reserved' );
		}
	}

	/* ---- Live slot loading ------------------------------------------- */

	function loadSlots() {
		var fields = getFields();
		if ( ! fields || ! cfg.ajaxUrl ) {
			return;
		}

		var locationEl = fields.querySelector( '[data-pickup-location]' );
		var dateEl = fields.querySelector( '[data-pickup-date]' );
		var slotEl = fields.querySelector( '[data-pickup-slot]' );
		var statusEl = fields.querySelector( '[data-pickup-status]' );

		if ( ! locationEl || ! dateEl || ! slotEl ) {
			return;
		}

		var location = locationEl.value;
		var date = dateEl.value;

		if ( ! location || ! date ) {
			resetSlots( slotEl, statusEl, i18n.choosePrompt || '' );
			return;
		}

		var blocked = cfg.blockedDates || [];
		if ( blocked.indexOf( date ) !== -1 ) {
			resetSlots( slotEl, statusEl, i18n.blockedDate || i18n.noSlots || '' );
			if ( statusEl ) {
				statusEl.classList.add( 'is-error' );
			}
			return;
		}

		if ( statusEl ) {
			statusEl.textContent = i18n.loading || '';
			statusEl.classList.remove( 'is-error' );
		}

		var body = new URLSearchParams();
		body.append( 'action', 'pickup_slots' );
		body.append( 'nonce', cfg.nonce || '' );
		body.append( 'location', location );
		body.append( 'date', date );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: body.toString(),
		} )
			.then( function ( res ) {
				return res.json();
			} )
			.then( function ( json ) {
				var slots =
					json && json.data && json.data.slots ? json.data.slots : [];
				renderSlots( slotEl, statusEl, slots );
			} )
			.catch( function () {
				if ( statusEl ) {
					statusEl.textContent = i18n.error || '';
					statusEl.classList.add( 'is-error' );
				}
			} );
	}

	function resetSlots( slotEl, statusEl, message ) {
		slotEl.innerHTML = '';
		var opt = document.createElement( 'option' );
		opt.value = '';
		opt.textContent = message;
		slotEl.appendChild( opt );
		if ( statusEl ) {
			statusEl.textContent = '';
			statusEl.classList.remove( 'is-error' );
		}
		syncReserved();
	}

	function renderSlots( slotEl, statusEl, slots ) {
		var previous = slotEl.value;
		slotEl.innerHTML = '';

		if ( ! slots.length ) {
			var none = document.createElement( 'option' );
			none.value = '';
			none.textContent = i18n.noSlots || '';
			slotEl.appendChild( none );
			if ( statusEl ) {
				statusEl.textContent = i18n.noSlots || '';
				statusEl.classList.add( 'is-error' );
			}
			return;
		}

		var placeholder = document.createElement( 'option' );
		placeholder.value = '';
		placeholder.textContent = '—';
		slotEl.appendChild( placeholder );

		slots.forEach( function ( slot ) {
			var opt = document.createElement( 'option' );
			opt.value = slot;
			opt.textContent = slot;
			if ( slot === previous ) {
				opt.selected = true;
			}
			slotEl.appendChild( opt );
		} );

		if ( statusEl ) {
			statusEl.textContent = '';
			statusEl.classList.remove( 'is-error' );
		}

		syncReserved();
	}

	/* ---- Wiring ------------------------------------------------------ */

	function bind() {
		var fields = getFields();
		if ( ! fields ) {
			return;
		}

		var locationEl = fields.querySelector( '[data-pickup-location]' );
		var dateEl = fields.querySelector( '[data-pickup-date]' );
		var slotEl = fields.querySelector( '[data-pickup-slot]' );

		if ( locationEl ) {
			locationEl.addEventListener( 'change', loadSlots );
		}
		if ( dateEl ) {
			dateEl.addEventListener( 'change', loadSlots );
		}
		if ( slotEl ) {
			slotEl.addEventListener( 'change', syncReserved );
		}

		// Reflect any posted-back / pre-selected slot on load.
		syncReserved();
	}

	document.body.addEventListener( 'change', function ( event ) {
		if (
			event.target &&
			event.target.name &&
			event.target.name.indexOf( 'shipping_method' ) === 0
		) {
			syncVisibility();
		}
	} );

	// WooCommerce re-renders the checkout after AJAX updates; re-bind then.
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_checkout', function () {
			syncVisibility();
			bind();
		} );
	}

	syncVisibility();
	bind();
} )();
