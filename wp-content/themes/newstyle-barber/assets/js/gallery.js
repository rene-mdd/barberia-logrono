/**
 * Gallery lightbox for Peluquería New Style.
 *
 * Opens the linked photos of any Gallery block with the "Cuadrícula con
 * ampliación" style (is-style-nsb-lightbox) in an accessible dialog.
 */
( function () {
	const t = window.nsbI18n || {};
	const items = Array.from( document.querySelectorAll( '.wp-block-gallery.is-style-nsb-lightbox .wp-block-image a' ) );
	if ( ! items.length ) {
		return;
	}

	// The dialog lives outside #page so the page can be made inert while it is open.
	const box = document.createElement( 'div' );
	box.className = 'nsb-lightbox';
	box.hidden = true;
	box.setAttribute( 'role', 'dialog' );
	box.setAttribute( 'aria-modal', 'true' );
	box.setAttribute( 'aria-label', t.dialog || '' );
	box.innerHTML =
		'<button type="button" class="nsb-lightbox__close"><span aria-hidden="true">&times;</span></button>' +
		'<button type="button" class="nsb-lightbox__nav nsb-lightbox__prev"><span aria-hidden="true">&#8249;</span></button>' +
		'<img class="nsb-lightbox__img" src="' + items[ 0 ].href + '" alt="">' +
		'<button type="button" class="nsb-lightbox__nav nsb-lightbox__next"><span aria-hidden="true">&#8250;</span></button>' +
		'<p class="nsb-lightbox__count" aria-live="polite"></p>';
	box.querySelector( '.nsb-lightbox__close' ).setAttribute( 'aria-label', t.close || 'Close' );
	box.querySelector( '.nsb-lightbox__prev' ).setAttribute( 'aria-label', t.prevImg || 'Previous' );
	box.querySelector( '.nsb-lightbox__next' ).setAttribute( 'aria-label', t.nextImg || 'Next' );
	document.body.appendChild( box );

	const img = box.querySelector( '.nsb-lightbox__img' );
	const buttons = Array.from( box.querySelectorAll( 'button' ) );
	const page = document.getElementById( 'page' );
	const count = box.querySelector( '.nsb-lightbox__count' );
	let current = 0;
	let opener = null;

	function show( index ) {
		current = ( index + items.length ) % items.length;
		const source = items[ current ];
		img.src = source.href;
		img.alt = source.querySelector( 'img' ).alt;
		count.textContent = ( current + 1 ) + ' / ' + items.length;
	}

	function open( index ) {
		opener = document.activeElement;
		show( index );
		box.hidden = false;
		document.body.classList.add( 'nsb-no-scroll' );
		// Hide the page behind the dialog from assistive technology.
		if ( page ) {
			page.setAttribute( 'inert', '' );
		}
		box.querySelector( '.nsb-lightbox__close' ).focus();
	}

	function close() {
		box.hidden = true;
		document.body.classList.remove( 'nsb-no-scroll' );
		if ( page ) {
			page.removeAttribute( 'inert' );
		}
		if ( opener ) {
			opener.focus();
		}
	}

	items.forEach( function ( item, index ) {
		item.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			open( index );
		} );
	} );

	box.querySelector( '.nsb-lightbox__close' ).addEventListener( 'click', close );
	box.querySelector( '.nsb-lightbox__prev' ).addEventListener( 'click', function () {
		show( current - 1 );
	} );
	box.querySelector( '.nsb-lightbox__next' ).addEventListener( 'click', function () {
		show( current + 1 );
	} );
	box.addEventListener( 'click', function ( event ) {
		if ( event.target === box ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( box.hidden ) {
			return;
		}
		if ( 'Tab' === event.key ) {
			// Keep keyboard focus inside the dialog.
			const first = buttons[ 0 ];
			const last = buttons[ buttons.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		} else if ( 'Escape' === event.key ) {
			close();
		} else if ( 'ArrowLeft' === event.key ) {
			show( current - 1 );
		} else if ( 'ArrowRight' === event.key ) {
			show( current + 1 );
		}
	} );
}() );
