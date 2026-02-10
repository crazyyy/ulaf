/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Root } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import MainLayout from '../../components/layout/main';
import { solidMailTheme } from '../../components/layout/theme';
import { FloatingSnackBar } from '../styles';

import ExportSettings from './export-setting';
import GeneralSettings from './general-setting';
import { SettingForm } from './styles';

function Settings() {
	const [settings, setSettings] = useState(solidMailSettings);
	const [toasts, setToasts] = useState([]);

	useEffect(() => {
		function fetchSettings() {
			apiFetch({ path: '/wp/v2/settings' })
				.then((wpSettings) =>
					setSettings(wpSettings.solid_mail_settings)
				)
				.catch((error) => {
					// eslint-disable-next-line no-console
					console.error('Failed to fetch settings:', error);
					addToast(__('Failed to fetch settings.', 'LION'));
				});
		}

		fetchSettings();
	}, []);

	function addToast(message) {
		const id = Date.now();
		setToasts((prevToasts) => [...prevToasts, { id, message }]);
	}

	function removeToast(id) {
		setToasts((prevToasts) =>
			prevToasts.filter((toast) => toast.id !== id)
		);
	}

	async function handleSubmit(event) {
		event.preventDefault(); // Prevent the default form submission

		try {
			await apiFetch({
				path: '/wp/v2/settings',
				method: 'POST',
				data: {
					solid_mail_settings: {
						...settings,
					},
				},
			});
			addToast(__('Settings saved successfully.', 'LION'));
		} catch (error) {
			// eslint-disable-next-line no-console
			console.error('Failed to save settings:', error);
			addToast(__('Failed to save settings.', 'LION'));
		}
	}

	return (
		<Root theme={solidMailTheme}>
			<MainLayout headerText={__('Settings', 'LION')} withNav={false}>
				<SettingForm method={'post'} onSubmit={handleSubmit}>
					<GeneralSettings
						settings={settings}
						setSettings={setSettings}
					/>
					<ExportSettings />
				</SettingForm>
				{toasts.length > 0 && (
					<FloatingSnackBar
						notices={toasts.map((toast) => ({
							id: toast.id,
							content: toast.message,
							status: 'default',
						}))}
						onRemove={(id) => {
							removeToast(id);
						}}
					/>
				)}
			</MainLayout>
		</Root>
	);
}

export default Settings;
