/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as actions from './actions';
import { STORE_NAME } from './constants';
import reducer from './reducer';
import * as resolvers from './resolvers';
import * as selectors from './selectors';

/**
 * Create and register the store.
 */
export const store = createReduxStore(STORE_NAME, {
	actions,
	selectors,
	resolvers,
	reducer,
});

register(store);
