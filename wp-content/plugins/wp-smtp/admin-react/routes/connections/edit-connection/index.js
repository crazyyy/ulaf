/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { arrowLeft, Icon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { STORE_NAME as connectionsStore } from '../../../data/src/connections/constants';
import ConnectionForm from '../connection-form';

import { Container, StyledLink } from './../add-connection/styles';

/**
 * The main component for edit an email service provider connection.
 */
function EditConnection() {
	const { id } = useParams();
	const { connection, isResolved } = useSelect(
		(select) => ({
			connection: select(connectionsStore).getConnectionById(id),
			isResolved: select(connectionsStore).hasFinishedResolution(
				'getConnectionById',
				[id]
			),
		}),
		[id]
	);

	const [model, setModel] = useState(connection);

	if (!isResolved) {
		return <Spinner />;
	}

	if (connection === null) {
		return (
			<Text variant={TextVariant.ACCENT}>
				{__('The connection does not exist.', 'LION')}
			</Text>
		);
	}

	return (
		<Container>
			<StyledLink to="/providers">
				<Icon icon={arrowLeft} size={20} />
				<Text variant={TextVariant.ACCENT}>
					{__('Back to Email Connections', 'LION')}
				</Text>
			</StyledLink>
			<ConnectionForm model={model} setModel={setModel} />
		</Container>
	);
}

export default EditConnection;
