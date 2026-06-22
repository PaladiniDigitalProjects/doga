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

	document.addEventListener( 'click', function ( e ) {
		const trigger = e.target.closest( '.pds-lb-trigger' );
		if ( trigger ) {
			const wrapper = trigger.closest( '.pds-lb-wrapper' );
			const overlay = wrapper && wrapper.querySelector( '.pds-lb-overlay' );
			if ( overlay ) openOverlay( overlay );
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
		if ( e.key !== 'Escape' ) return;
		document.querySelectorAll( '.pds-lb-overlay.is-active' ).forEach( closeOverlay );
	} );
} )();
