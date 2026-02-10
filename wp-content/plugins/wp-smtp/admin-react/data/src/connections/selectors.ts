import { createSelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ConnectionProvider } from './constants';
import { State, Connection } from './types';

export function getConnections(state: State): Record<string, Connection> {
	return state.connections;
}

export function getTexts(state: State): State['texts'] {
	return state.texts;
}

export function getAvailableConnections(
	state: State
): State['availableConnections'] {
	return state.availableConnections;
}

export function getErrors(state: State): State['errors'] {
	return state.errors;
}

export function getConnectionById(state: State, id: string): Connection | null {
	return state.connections[id] ?? null;
}

export function getToasts(state: State): State['toasts'] {
	return state.toasts;
}

export const getActiveConnections = createSelector(
	(state: State): Connection[] => {
		return Object.values(state.connections).filter(
			(connection) => connection.is_active
		);
	},
	(state: State) => {
		return [...Object.values(state.connections)];
	}
);

export const getDefaultConnection = createSelector(
	(state: State): Connection | undefined => {
		return Object.values(state.connections).find(
			(connection) => connection.is_default
		);
	},
	(state: State) => {
		return [...Object.values(state.connections)];
	}
);

export function getConnectorDisplayName(
	state: State,
	provider: ConnectionProvider
): string {
	return state.availableConnections[provider] || provider;
}
