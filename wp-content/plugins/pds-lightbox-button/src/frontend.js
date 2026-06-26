( function () {
	'use strict';

	function openOverlay( overlay ) {
		overlay.classList.add( 'is-active' );
		document.body.style.overflow = 'hidden';
		const closeBtn = overlay.querySelector( '.pds-lb-close' );
		if ( closeBtn ) closeBtn.focus();
	}

	function closeOverlay( overlay ) {
		overlay.classList.remove( 'is-active' );
		document.body.style.overflow = '';
	}

	function openFromTrigger( trigger ) {
		const wrapper = trigger.closest( '.pds-lb-wrapper' );
		const overlay = wrapper && wrapper.querySelector( '.pds-lb-overlay' );
		if ( ! overlay ) return;

		// Inyecta la URL del recurso en el campo oculto de WPForms que haya dentro de ESTE
		// lightbox (campo con clase CSS "pds-lb-download"). La URL viene en data-pds-download
		// (no en href): así el <a> no es navegable y ni Luge ni el navegador pueden "saltar".
		const href =
			trigger.getAttribute( 'data-pds-download' ) ||
			trigger.getAttribute( 'href' );
		if ( href && href !== '#' ) {
			const field = overlay.querySelector(
				'.pds-lb-download input, input.pds-lb-download'
			);
			if ( field ) {
				field.value = href;
			}
		}

		openOverlay( overlay );
	}

	// En FASE DE CAPTURA: el trigger es un <a href="recurso">, y en esta web conviven otros
	// scripts (pds-query-lightbox, predictive-search…) que hacen stopPropagation. Si corren
	// antes, nuestro handler en burbujeo no llegaría y el <a> navegaría. Capturando, corremos
	// primero y garantizamos el preventDefault.
	document.addEventListener(
		'click',
		function ( e ) {
			const trigger = e.target.closest( '.pds-lb-trigger' );
			if ( trigger ) {
				e.preventDefault();
				openFromTrigger( trigger );
				return;
			}

			const closeBtn = e.target.closest( '.pds-lb-close' );
			if ( closeBtn ) {
				const overlay = closeBtn.closest( '.pds-lb-overlay' );
				if ( overlay ) closeOverlay( overlay );
				return;
			}

			// Click on the backdrop itself (not on the modal content)
			if ( e.target.classList.contains( 'pds-lb-overlay' ) ) {
				closeOverlay( e.target );
			}
		},
		true
	);

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			document.querySelectorAll( '.pds-lb-overlay.is-active' ).forEach( closeOverlay );
			return;
		}

		// El trigger es un <a role="button"> → activarlo con Enter/Espacio como un botón nativo.
		if ( e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar' ) {
			const trigger = e.target.closest && e.target.closest( '.pds-lb-trigger' );
			if ( trigger ) {
				e.preventDefault();
				openFromTrigger( trigger );
			}
		}
	} );
} )();
