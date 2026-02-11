/**
 * External dependencies
 */
import { produce } from 'immer';

/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { DEFAULT_STATE, ActionType } from './constants';
import { State, Action } from './types';

const EMPTY_OBJECT: Record<string, never> = {};

function connections(
	state: State['connections'] = DEFAULT_STATE.connections,
	action: Action
): State['connections'] {
	switch (action.type) {
		case ActionType.SetConnections:
			return action.connections.reduce<State['connections']>(
				(acc, connection) => {
					acc[connection.id] = connection;
					return acc;
				},
				{}
			);

		case ActionType.SetConnection:
			return produce(state, (draft) => {
				draft[action.connection.id] = action.connection;
				if (action.connection.is_default) {
					Object.keys(draft).forEach((id) => {
						draft[id].is_default = id === action.connection.id;
					});
				}
			});

		case ActionType.RemoveConnection:
			return produce(state, (draft) => {
				delete draft[action.connectionId];
			});

		default:
			return state;
	}
}

function toasts(
	state: State['toasts'] = DEFAULT_STATE.toasts,
	action: Action
): State['toasts'] {
	switch (action.type) {
		case ActionType.AddToast:
			return produce(state, (draft) => {
				draft.push(action.toast);
			});

		case ActionType.RemoveToast:
			return state.filter((toast) => toast.id !== action.toastId);

		default:
			return state;
	}
}

function errors(
	state: State['errors'] = DEFAULT_STATE.errors,
	action: Action
): State['errors'] {
	switch (action.type) {
		case ActionType.AddErrors:
			return action.errors;

		case ActionType.ClearErrors:
			return EMPTY_OBJECT;

		default:
			return state;
	}
}

function availableConnections(
	state: State['availableConnections'] = DEFAULT_STATE.availableConnections
): State['availableConnections'] {
	return state;
}

function texts(state: State['texts'] = DEFAULT_STATE.texts): State['texts'] {
	return state;
}

export default combineReducers({
	connections,
	toasts,
	errors,
	availableConnections,
	texts,
});
