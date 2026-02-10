/**
 * External dependencies
 */
import { Outlet } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Root } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import '../data/src/connections/index';
import '../assets/scss/Root.scss';
import MainLayout from '../components/layout/main';
import { solidMailTheme } from '../components/layout/theme';
import { STORE_NAME } from '../data/src/connections/constants';

import { FloatingSnackBar } from './styles';

/**
 * SnackbarNotification component
 *
 * Displays notifications using the FloatingSnackBar component.
 *
 * @return {JSX.Element} The SnackbarNotification component.
 */
function SnackbarNotification() {
	const toasts = useSelect((select) => select(STORE_NAME).getToasts(), []);
	const { removeToast } = useDispatch(STORE_NAME);
	return (
		<div>
			{toasts.length > 0 && (
				<FloatingSnackBar
					notices={toasts.map((toast) => ({
						id: toast.id,
						content: toast.message,
						status: toast.status,
					}))}
					onRemove={(id) => {
						removeToast(id);
					}}
				/>
			)}
		</div>
	);
}

/**
 * Root component
 *
 * Provides the main layout and theme for the application.
 *
 * @return {JSX.Element} The Root component.
 */
function MailRoot() {
	return (
		<Root theme={solidMailTheme}>
			<MainLayout headerText={__('Email Connections', 'LION')}>
				<Outlet />
				<SnackbarNotification />
			</MainLayout>
		</Root>
	);
}

export default MailRoot;
