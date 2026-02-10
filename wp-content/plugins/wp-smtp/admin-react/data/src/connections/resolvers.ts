/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import { ActionType, ToastType } from './constants';
import { Connection, Thunk } from './types';

export const getConnections =
	(): Thunk =>
	async ({ dispatch }) => {
		try {
			const connections = await apiFetch<Array<Connection>>({
				path: '/solid-mail/v1/connections',
				method: 'GET',
			});

			dispatch({
				type: ActionType.SetConnections,
				connections,
			});
		} catch (_error) {
			dispatch.addToast(
				__('Failed to fetch connections.', 'LION'),
				ToastType.Error
			);
		}
	};

/**
 * Fetches a connection by ID from the REST API.
 *
 * @return {Function} Thunk function.
 */
export const getConnectionById =
	(): Thunk =>
	async ({ resolveSelect }) => {
		await resolveSelect.getConnections();
	};
