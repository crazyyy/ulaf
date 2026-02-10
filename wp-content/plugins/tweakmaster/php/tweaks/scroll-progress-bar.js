window.addEventListener(
	'scroll',
	throttle( () => {
		handleScroll();
	} )
);

function handleScroll() {
	const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
	const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
	const scrolled = ( winScroll / height ) * 100;
	document.querySelector( '.tweakmaster-scroll__progress' ).style.width = scrolled + '%';
}

/**
 * Throttle a function to prevent it from being called too quickly.
 *
 * The function will be called after the browser has finished rendering the
 * current frame, and will be passed the arguments that were passed to the
 * throttled function on the last call.
 *
 * @param {Function} callback The function to be throttled
 * @return {Function} The throttled function
 */
function throttle( callback ) {
	let requestId = null;
	let lastArgs;

	const later = ( context ) => () => {
		requestId = null;
		callback.apply( context, lastArgs );
	};

	const throttled = function ( ...args ) {
		lastArgs = args;
		if ( requestId === null ) {
			requestId = requestAnimationFrame( later( this ) );
		}
	};

	return throttled;
}
