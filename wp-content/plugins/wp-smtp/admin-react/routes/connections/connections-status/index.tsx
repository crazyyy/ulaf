import { useSelect } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Text, TextVariant } from '@ithemes/ui';

import { store as connectionsStore } from '../../../data/src/connections';

export default function ConnectionsStatus() {
	const { activeConnections, defaultConnection } = useSelect(
		(select) => ({
			activeConnections: select(connectionsStore).getActiveConnections(),
			defaultConnection: select(connectionsStore).getDefaultConnection(),
		}),
		[]
	);

	if (activeConnections.length === 0) {
		return (
			<Text
				text={__(
					'At least one connection must be activated for Solid Mail to work.',
					'LION'
				)}
				variant={TextVariant.MUTED}
			/>
		);
	}

	if (!defaultConnection) {
		return (
			<Text
				text={createInterpolateElement(
					__(
						'We suggest enabling a <help>default connection</help> to ensure all emails are delivered by Solid Mail.',
						'LION'
					),
					{
						help: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a
								href="https://go.solidwp.com/mail-set-up-connection"
								rel="noreferrer"
								target="_blank"
							/>
						),
					}
				)}
				variant={TextVariant.WARNING}
			/>
		);
	}

	return null;
}
