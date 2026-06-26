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
		if ( overlay ) openOverlay( overlay );
	}

	document.addEventListener( 'click', function ( e ) {
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
	} );

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
