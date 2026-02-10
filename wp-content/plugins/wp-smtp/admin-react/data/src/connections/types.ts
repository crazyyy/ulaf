/**
 * Internal dependencies
 */
import * as actions from './actions';
import { ConnectionProvider, ToastType, ActionType } from './constants';
import * as selectors from './selectors';

export interface Connection {
	id: string;
	provider: ConnectionProvider;
	smtp_host: string;
	smtp_port: number;
	smtp_secure: 'none' | 'ssl' | 'tls';
	smtp_username: string;
	smtp_password: string;
	from_email: string;
	from_name: string;
	smtp_auth: boolean;
	is_active: boolean;
	is_default: boolean;
}

export interface Toast {
	id: string;
	message: string;
	type: ToastType;
}

export interface State {
	connections: {
		[key: string]: Connection;
	};
	availableConnections: {
		[key in ConnectionProvider]: string;
	};
	errors: {
		[key: string]: string;
	};
	texts: {
		select_provider: string;
		smtp_host: string;
		smtp_port: string;
		smtp_secure: string;
		smtp_username: string;
		smtp_password: string;
		from_email: string;
		from_name: string;
		smtp_auth: string;
	};
	toasts: Toast[];
}

export type Action =
	| {
			type: ActionType.SetConnections;
			connections: Connection[];
	  }
	| {
			type: ActionType.SetConnection;
			connection: Connection;
	  }
	| {
			type: ActionType.RemoveConnection;
			connectionId: string;
	  }
	| {
			type: ActionType.AddToast;
			toast: Toast;
	  }
	| {
			type: ActionType.RemoveToast;
			toastId: string;
	  }
	| {
			type: ActionType.AddErrors;
			errors: Record<string, string>;
	  }
	| {
			type: ActionType.ClearErrors;
	  };

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type CurriedState<F> = F extends (state: any, ...args: infer P) => infer R
	? (...args: P) => R
	: F;

type CurriedSelectors<Selectors> = {
	[key in keyof Selectors]: CurriedState<Selectors[key]>;
};

// eslint-disable-next-line @typescript-eslint/no-unsafe-function-type
type Promisify<F extends Function> = F extends (...args: infer P) => infer R
	? (...args: P) => Promise<R>
	: F;

// eslint-disable-next-line @typescript-eslint/no-unsafe-function-type
type PromisifiedSelectors<Selectors extends Record<string, Function>> = {
	[key in keyof Selectors]: Promisify<Selectors[key]>;
};

/**
 * Thunk type for async actions
 */
export type ThunkArgs<A extends Action> = {
	dispatch: typeof actions & ((action: A) => void);
	select: CurriedSelectors<typeof selectors>;
	resolveSelect: PromisifiedSelectors<CurriedSelectors<typeof selectors>>;
};

export type Thunk = (args: ThunkArgs<Action>) => Promise<void>;

export type WpError = {
	code: string;
	message: string;
	additional_errors: Array<{
		code: string;
		message: string;
	}>;
};
