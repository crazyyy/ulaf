/**
 * WordPress dependencies
 */
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function GeneralSettings({ settings, setSettings }) {
	function handleToggleChange(newValue) {
		setSettings((prevSettings) => ({
			...prevSettings,
			disable_logs: newValue ? 'no' : 'yes',
		}));
	}

	function handleUnmatchedConnectionsChange(newValue) {
		setSettings((prevSettings) => ({
			...prevSettings,
			use_unmatched_connections: newValue ? 'yes' : 'no',
		}));
	}

	return (
		<Card>
			<CardHeader>
				<h2>{__('General', 'LION')}</h2>
			</CardHeader>
			<CardBody>
				<ToggleControl
					label={__('Enable Logs', 'LION')}
					checked={settings.disable_logs === 'no'}
					onChange={handleToggleChange}
				/>
				<ToggleControl
					label={__('Use alternative connections on failure', 'LION')}
					help={__(
						'If the default connection fails then an alternative connection will be used as a fallback. Connections with the same "from" address are prioritized.',
						'LION'
					)}
					checked={settings.use_unmatched_connections === 'yes'}
					onChange={handleUnmatchedConnectionsChange}
				/>
				<Button variant={'primary'} type={'submit'}>
					{__('Save', 'LION')}
				</Button>
			</CardBody>
		</Card>
	);
}

export default GeneralSettings;
