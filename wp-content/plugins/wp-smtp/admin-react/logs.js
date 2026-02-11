/**
 * External dependencies
 */
import { createHashRouter, RouterProvider } from 'react-router-dom';

import { createRoot } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Logs from './routes/logs';

/**
 * Sets up the router configuration.
 */
const router = createHashRouter([
	{
		path: '/',
		element: <Logs />,
	},
]);

/**
 * Retrieves the root element and initializes the React application.
 */
const rootElement = document.getElementById('solidwp-mail-root');
const root = createRoot(rootElement);

/**
 * Renders the application with the RouterProvider.
 */
root.render(<RouterProvider router={router} />);
