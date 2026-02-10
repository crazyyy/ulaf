/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from '../../data/src/connections/constants';

import ConnectionsList from './connections-list';
import EmptyConnections from './empty-connections';
import { Container } from './styles';

function Connections() {
	const { connections, hasFinishedResolution } = useSelect(
		(select) => ({
			connections: select(STORE_NAME).getConnections(),
			hasFinishedResolution:
				select(STORE_NAME).hasFinishedResolution('getConnections'),
		}),
		[]
	);

	if (!hasFinishedResolution) {
		return (
			<Container>
				<Spinner />
			</Container>
		);
	}

	const connectionsList = Object.values(connections);

	return (
		<Container>
			{connectionsList.length === 0 ? (
				<EmptyConnections />
			) : (
				<ConnectionsList connections={connectionsList} />
			)}
		</Container>
	);
}

export default Connections;
