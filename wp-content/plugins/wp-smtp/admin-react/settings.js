/**
 * External Dependencies
 */
import { createHashRouter, RouterProvider } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Settings from './routes/settings';

const router = createHashRouter([
	{
		element: <Settings />,
		index: true,
	},
]);

const rootElement = document.getElementById('solidwp-mail-root');
const root = createRoot(rootElement);
root.render(<RouterProvider router={router} />);
