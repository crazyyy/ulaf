/**
 * External Dependencies
 */
import { createHashRouter, Navigate, RouterProvider } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Connections from './routes/connections';
import AddConnection from './routes/connections/add-connection';
import EditConnection from './routes/connections/edit-connection';
import EmailTest from './routes/email-test';
import Root from './routes/Root';

const router = createHashRouter([
	{
		path: '/providers',
		element: <Root />,
		children: [
			{
				index: true,
				element: <Connections />,
			},
			{
				path: 'add',
				element: <AddConnection />,
			},
			{
				path: 'edit/:id',
				element: <EditConnection />,
			},
		],
	},
	{
		path: 'email-test',
		element: <Root />,
		children: [
			{
				index: true,
				element: <EmailTest />,
			},
		],
	},
	{
		path: '*',
		element: <Navigate to="/providers" replace />,
	},
]);

const rootElement = document.getElementById('solidwp-mail-root');
const root = createRoot(rootElement);
root.render(<RouterProvider router={router} />);
