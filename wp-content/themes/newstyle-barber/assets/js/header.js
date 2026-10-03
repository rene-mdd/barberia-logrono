/**
 * Exposes the space taken by the sticky header (its height plus its sticky
 * offset, e.g. the admin bar) as --nsb-header-h so anchor links
 * (#services, #gallery…) scroll to just below the menu.
 */
( function () {
	const header = document.getElementById( 'masthead' );
	if ( ! header ) {
		return;
	}

	function update() {
		const top = parseFloat( getComputedStyle( header ).top ) || 0;
		document.documentElement.style.setProperty( '--nsb-header-h', ( header.offsetHeight + top ) + 'px' );
	}

	update();
	window.addEventListener( 'resize', update );
	window.addEventListener( 'load', update );
}() );
