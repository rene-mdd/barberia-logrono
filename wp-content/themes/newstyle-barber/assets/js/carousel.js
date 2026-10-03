/**
 * "Espacio" carousel for Peluquería New Style.
 *
 * Enhances any Gallery block with the "Carrusel" style (is-style-nsb-carousel)
 * into a scroll-snap carousel with prev/next buttons, dots and an autoplay
 * that visitors can pause (WCAG 2.2.2). Without JavaScript the gallery stays a
 * normal grid. Autoplay is off for people who prefer reduced motion.
 */
( function () {
	const t = window.nsbI18n || {};

	function button( className, label, html ) {
		const el = document.createElement( 'button' );
		el.type = 'button';
		el.className = className;
		if ( label ) {
			el.setAttribute( 'aria-label', label );
		}
		el.innerHTML = html;
		return el;
	}

	document.querySelectorAll( '.wp-block-gallery.is-style-nsb-carousel' ).forEach( function ( track ) {
		const slides = Array.from( track.querySelectorAll( ':scope > .wp-block-image' ) );
		if ( slides.length < 2 ) {
			return;
		}

		// Build: region > [controls, prev, track, next] + dots.
		const carousel = document.createElement( 'div' );
		carousel.className = 'nsb-carousel';
		carousel.setAttribute( 'role', 'region' );
		carousel.setAttribute( 'aria-roledescription', 'carrusel' );
		carousel.setAttribute( 'aria-label', t.carousel || 'Fotos' );

		const controls = document.createElement( 'div' );
		controls.className = 'nsb-carousel__controls';
		const toggle = button( 'nsb-carousel__toggle', '', '<span class="nsb-carousel__toggle-icon" aria-hidden="true"></span><span class="nsb-carousel__toggle-text"></span>' );
		controls.appendChild( toggle );
		const toggleText = toggle.querySelector( '.nsb-carousel__toggle-text' );

		const prev = button( 'nsb-carousel__btn nsb-carousel__prev', t.prev, '<span aria-hidden="true">&#8249;</span>' );
		const next = button( 'nsb-carousel__btn nsb-carousel__next', t.next, '<span aria-hidden="true">&#8250;</span>' );
		const dotsWrap = document.createElement( 'div' );
		dotsWrap.className = 'nsb-carousel__dots';

		track.parentNode.insertBefore( carousel, track );
		carousel.append( controls, prev, track, next );
		carousel.after( dotsWrap );

		track.classList.add( 'nsb-carousel__track' );
		track.setAttribute( 'tabindex', '0' );
		track.setAttribute( 'aria-label', t.scroll || t.carousel || 'Fotos' );
		slides.forEach( function ( slide ) {
			slide.classList.add( 'nsb-carousel__slide' );
		} );

		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const behavior = reduceMotion ? 'auto' : 'smooth';
		let timer = null;
		let target = 0;
		let paused = reduceMotion;
		let hovering = false;

		function step() {
			return slides[ 1 ].getBoundingClientRect().left - slides[ 0 ].getBoundingClientRect().left;
		}

		function currentIndex() {
			return Math.round( track.scrollLeft / step() );
		}

		function lastIndex() {
			return Math.max( 0, Math.round( ( track.scrollWidth - track.clientWidth ) / step() ) );
		}

		function goTo( index ) {
			const last = lastIndex();
			if ( index > last ) {
				index = 0;
			} else if ( index < 0 ) {
				index = last;
			}
			target = index;
			updateDots();
			track.scrollTo( { left: index * step(), behavior: behavior } );
		}

		// Dots: one per reachable scroll position.
		let dots = [];
		function buildDots() {
			dotsWrap.innerHTML = '';
			dots = [];
			for ( let i = 0; i <= lastIndex(); i++ ) {
				const dot = button( 'nsb-carousel__dot', ( t.goTo || '%d' ).replace( '%d', i + 1 ), '' );
				dot.addEventListener( 'click', function () {
					goTo( i );
				} );
				dotsWrap.appendChild( dot );
				dots.push( dot );
			}
			updateDots();
		}

		function updateDots() {
			dots.forEach( function ( dot, i ) {
				if ( i === target ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );
		}

		prev.addEventListener( 'click', function () {
			goTo( target - 1 );
		} );
		next.addEventListener( 'click', function () {
			goTo( target + 1 );
		} );

		// Keep the target in sync when the visitor swipes or scrolls by hand.
		let scrollTimer = null;
		track.addEventListener( 'scroll', function () {
			clearTimeout( scrollTimer );
			scrollTimer = setTimeout( function () {
				target = currentIndex();
				updateDots();
			}, 120 );
		} );

		let resizeTimer = null;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( buildDots, 150 );
		} );

		// Autoplay: runs unless paused with the button, or while the pointer or
		// keyboard focus is inside the carousel.
		function sync() {
			const shouldRun = ! paused && ! hovering;
			if ( shouldRun && ! timer ) {
				timer = setInterval( function () {
					if ( ! document.hidden ) {
						goTo( target + 1 );
					}
				}, 5000 );
			} else if ( ! shouldRun && timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function renderToggle() {
			toggle.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
			toggle.classList.toggle( 'is-paused', paused );
			toggleText.textContent = paused ? t.play || 'Play' : t.pause || 'Pause';
		}

		toggle.addEventListener( 'click', function () {
			paused = ! paused;
			renderToggle();
			sync();
		} );

		carousel.addEventListener( 'mouseenter', function () {
			hovering = true;
			sync();
		} );
		carousel.addEventListener( 'mouseleave', function () {
			hovering = false;
			sync();
		} );
		carousel.addEventListener( 'focusin', function () {
			hovering = true;
			sync();
		} );
		carousel.addEventListener( 'focusout', function ( event ) {
			if ( ! carousel.contains( event.relatedTarget ) ) {
				hovering = false;
				sync();
			}
		} );
		// Swiping means the visitor has taken control: stop autoplay.
		track.addEventListener(
			'touchstart',
			function () {
				paused = true;
				renderToggle();
				sync();
			},
			{ passive: true }
		);

		buildDots();
		renderToggle();
		sync();
	} );
}() );
