/**
 * External dependencies
 */
import { produce } from 'immer';

/**
 * Internal dependencies
 */
import {
	DELETE_LOGS,
	IS_SEARCHING,
	SET_CURRENT_PAGE,
	SET_LOGS,
	SET_SELECTED_LOG,
	STOP_SEARCHING,
} from './actions';
import { DEFAULT_STATE } from './constants';

/**
 * Reducer to handle state changes.
 *
 * @param {Object} state  - The current state.
 * @param {Object} action - The action object.
 * @return {Object} The new state.
 */
export default function reducer(state = DEFAULT_STATE, action) {
	switch (action.type) {
		case SET_LOGS:
			return produce(state, (draft) => {
				draft.logs = action.data.logs;
				draft.totalPages = action.data.totalPages;
				if (action.data.totalLogs > 0) {
					draft.selectedLog = action.data.logs[0];
				}
			});
		case IS_SEARCHING:
			return produce(state, (draft) => {
				draft.isSearching = true;
			});
		case STOP_SEARCHING:
			return produce(state, (draft) => {
				draft.isSearching = false;
			});
		case DELETE_LOGS:
			return state;
		case SET_SELECTED_LOG:
			return produce(state, (draft) => {
				draft.selectedLog = action.log;
			});
		case SET_CURRENT_PAGE:
			return produce(state, (draft) => {
				draft.currentPage = action.page;
			});
		default:
			return state;
	}
}
