const d = document;
const qs = d.querySelector.bind( d );
const link = qs( '#wp-admin-bar-my-account > .ab-item' );
const displayName = qs( '#wp-admin-bar-my-account > .ab-item > .display-name' );
const greeting = qs( '[data-tweakmaster-greeting]' )?.dataset.tweakmasterGreeting;
const avatar = qs( '#wp-admin-bar-my-account > .ab-item > .avatar' );

if ( ! link || ! displayName || ! greeting ) {
	throw new Error( 'Missing elements' );
}

link.innerHTML = '';
newLinkHTML();

if ( avatar ) {
	link.appendChild( avatar );
}

/**
 * Create the HTML elements for the new greeting text inside the admin bar link.
 * If the greeting contains the {{name}} placeholder, split the greeting into
 * three parts (before, name, after) and create separate elements for each part.
 * If the greeting does not contain the placeholder, just create a single text element
 * with the greeting as its content.
 */
function newLinkHTML() {
	const parts = greeting.split( '{name}' );
	if ( parts.length === 1 ) {
		// no {{name}} placeholder found, just create a single text element
		const textElement = d.createElement( 'span' );
		textElement.textContent = greeting;
		link.appendChild( textElement );
	} else {
		// {{name}} placeholder found, create separate elements for before and after
		const textBefore = document.createTextNode( parts[ 0 ] );
		const textAfter = document.createTextNode( parts[ 1 ] );

		link.appendChild( textBefore );
		link.appendChild( displayName );
		link.appendChild( textAfter );
	}
}
