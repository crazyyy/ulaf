/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, arrowLeft } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledSurface } from '../../../assets/common';
import { FormSelect } from '../../../components/form';
import { STORE_NAME as connectionsStore } from '../../../data/src/connections/constants';
import Provider from '../../../model/provider';
import ConnectionForm from '../connection-form';

import { Container, StyledLink } from './styles';

/**
 * Retrieves the options for the provider select dropdown.
 *
 * @return {Array} The options for the provider select dropdown.
 */
function useProviderSelectionOptions() {
	const options = [
		{
			value: '',
			label: __('---Select Provider---', 'LION'),
		},
	];
	const availableConnections = useSelect(
		(select) => select(connectionsStore).getAvailableConnections(),
		[]
	);
	Object.entries(availableConnections).forEach(([key, value]) => {
		options.push({ value: key, label: value });
	});

	return options;
}

/**
 * The main component for adding a new email service provider connection.
 */
function AddConnection() {
	const [provider, setProvider] = useState('');
	const [model, setModel] = useState(null);
	const { texts } = useSelect(
		(select) => ({
			texts: select(connectionsStore).getTexts(),
		}),
		[]
	);

	/**
	 * Handles the selection of a provider from the dropdown.
	 *
	 * @param {string} value - The selected provider value.
	 */
	function handleProviderSelect(value) {
		setProvider(value);
		if (value) {
			const obj = new Provider('', value);
			setModel(obj);
		} else {
			setModel(null);
		}
	}

	return (
		<Container>
			<StyledLink to="/providers">
				<Icon icon={arrowLeft} size={20} />
				<Text variant={TextVariant.ACCENT}>
					{__('Back to Email Connections', 'LION')}
				</Text>
			</StyledLink>
			<StyledSurface>
				<FormSelect
					label={__('Provider', 'LION')}
					value={provider}
					onChange={handleProviderSelect}
					options={useProviderSelectionOptions()}
					description={texts.select_provider}
				/>
			</StyledSurface>
			{model !== null && (
				<ConnectionForm model={model} setModel={setModel} />
			)}
		</Container>
	);
}

export default AddConnection;
