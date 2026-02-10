/**
 * Internal dependencies
 */
import { queryLogs, deleteLogs, clearAllLogs } from './controls';

/**
 * Redux action creators and selectors for managing logs.
 */

/**
 * Generator function to populate logs based on the current page and search query.
 *
 * @param {number} page   - The current page number.
 * @param {string} search - The search query.
 * @return {Object} The action to set logs.
 */
export function* populateLogs(page, search) {
	const data = yield queryLogs(page, search);
	if (search) {
		yield {
			type: IS_SEARCHING,
		};
	} else {
		yield {
			type: STOP_SEARCHING,
		};
	}
	return setLogs(data);
}

/**
 * Action creator to set logs data.
 *
 * @param {Array} data - The logs data.
 * @return {Object} The action object.
 */
export function setLogs(data) {
	return {
		type: SET_LOGS,
		data,
	};
}

export function* setCurrentPage(page, search) {
	// fetch the logs
	const logs = yield queryLogs(page, search);
	// update the state
	yield setLogs(logs);
	return {
		type: SET_CURRENT_PAGE,
		page,
	};
}

/**
 * Generator function to delete a log and refresh the logs data.
 *
 * @param {number[]} IDs  - The ID of the log to delete.
 * @param {number}   page - The current page number.
 * @return {Object} The action to set logs after deletion.
 */
export function* deleteLog(IDs, page) {
	yield deleteLogs(IDs);
	// Refresh the data.
	const logs = yield queryLogs(page, '');
	return setLogs(logs);
}

/**
 * Generator function to clear all logs and refresh the logs data.
 *
 * @param {number} page - The current page number.
 * @return {Object} The action to set logs after clearing all.
 */
export function* clearAllLogsAction(page) {
	yield clearAllLogs();
	// Refresh the data.
	const logs = yield queryLogs(page, '');
	return setLogs(logs);
}

/**
 * Action creator to select a log.
 *
 * @param {Object} log - The log object to select.
 * @return {Object} The action object.
 */
export function selectLog(log) {
	return {
		type: SET_SELECTED_LOG,
		log,
	};
}

// Action types
export const SET_LOGS = 'SET_LOGS';
export const DELETE_LOGS = 'DELETE_LOGS';
export const SET_SELECTED_LOG = 'SET_SELECTED_LOG';
export const SET_CURRENT_PAGE = 'SET_CURRENT_PAGE';
export const IS_SEARCHING = 'IS_SEARCHING';
export const STOP_SEARCHING = 'STOP_SEARCHING';
