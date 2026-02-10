/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const API_BASE = '/solidwp-mail/v1/logs';

/**
 * Action creator to query logs.
 *
 * @param {number} page   - The page number.
 * @param {string} search - The search term.
 * @return {Object} The action object.
 */
export const queryLogs = (page, search) => {
	return {
		type: 'QUERY_LOGS',
		page,
		search,
	};
};

/**
 * Action creator to delete logs.
 *
 * @param {Array} logIDs - The IDs of the logs to delete.
 * @return {Object} The action object.
 */
export const deleteLogs = (logIDs) => {
	return {
		type: 'DELETE_LOGS',
		logIDs,
	};
};

/**
 * Action creator to clear all logs.
 *
 * @return {Object} The action object.
 */
export const clearAllLogs = () => {
	return {
		type: 'CLEAR_ALL_LOGS',
	};
};

/**
 * Control handlers for querying and deleting logs.
 */
const controls = {
	QUERY_LOGS({ page, search }) {
		const query = {
			page,
			search_term: search,
		};
		return apiFetch({
			path: addQueryArgs(`${API_BASE}`, query),
			method: 'GET',
			parse: false,
		})
			.then((response) => {
				const totalPages = response.headers.get('X-WP-TotalPages');
				const totalLogs = response.headers.get('X-WP-Total');
				return response.json().then((data) => ({
					logs: data.logs,
					totalPages: parseInt(totalPages, 10),
					totalLogs: parseInt(totalLogs, 10),
				}));
			})
			.catch((error) => {
				throw new Error(error.message());
			});
	},
	DELETE_LOGS({ logIDs }) {
		return apiFetch({
			path: `${API_BASE}/delete`,
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				logIds: logIDs,
			}),
		}).catch((error) => {
			throw new Error(error.message());
		});
	},
	CLEAR_ALL_LOGS() {
		return apiFetch({
			path: `${API_BASE}`,
			method: 'DELETE',
		}).catch((error) => {
			throw new Error(error.message());
		});
	},
};

export default controls;
