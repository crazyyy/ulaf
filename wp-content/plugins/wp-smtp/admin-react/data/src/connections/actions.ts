/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ToastType, ActionType } from './constants';
import { Connection, Thunk, Action } from './types';
import { convertToWpError } from './utils';

export const addConnection =
	(data: Connection): Thunk =>
	async ({ dispatch }) => {
		try {
			const connection = await apiFetch<Connection>({
				path: '/solid-mail/v1/connections',
				method: 'POST',
				data,
			});

			dispatch({
				type: ActionType.SetConnection,
				connection,
			});

			dispatch(
				addToast(
					__('The connection has been added successfully.', 'LION')
				)
			);
		} catch (error) {
			const wpError = convertToWpError(error);

			if (wpError.code === 'invalid_connection_data') {
				dispatch({
					type: ActionType.AddErrors,
					errors: wpError.additional_errors.reduce(
						(
							acc: Record<string, string>,
							{ code, message }: { code: string; message: string }
						) => {
							acc[code] = message;
							return acc;
						},
						{}
					),
				});
			} else {
				dispatch(
					addToast(
						[
							__('Failed to add connection.', 'LION'),
							wpError.message,
						]
							.filter(Boolean)
							.join(' '),
						ToastType.Error
					)
				);
			}
		}
	};

export const updateConnection =
	(id: string, data: Partial<Connection>): Thunk =>
	async ({ dispatch }) => {
		try {
			const connection = await apiFetch<Connection>({
				path: `/solid-mail/v1/connections/${id}`,
				method: 'PUT',
				data,
			});

			dispatch({
				type: ActionType.SetConnection,
				connection,
			});

			dispatch(
				addToast(
					__('The connection has been updated successfully.', 'LION')
				)
			);
		} catch (error) {
			const wpError = convertToWpError(error);

			if (wpError.code === 'invalid_connection_data') {
				dispatch({
					type: ActionType.AddErrors,
					errors: wpError.additional_errors.reduce(
						(
							acc: Record<string, string>,
							{ code, message }: { code: string; message: string }
						) => {
							acc[code] = message;
							return acc;
						},
						{}
					),
				});
			} else {
				dispatch(
					addToast(
						[
							__('Failed to update connection.', 'LION'),
							wpError.message,
						]
							.filter(Boolean)
							.join(' '),
						ToastType.Error
					)
				);
			}
		}
	};

export const deleteConnection =
	(id: string): Thunk =>
	async ({ dispatch }) => {
		try {
			await apiFetch({
				path: `/solid-mail/v1/connections/${id}`,
				method: 'DELETE',
			});

			dispatch({
				type: ActionType.RemoveConnection,
				connectionId: id,
			});

			dispatch(addToast(__('The connection has been deleted.', 'LION')));
		} catch (error) {
			const wpError = convertToWpError(error);

			dispatch(
				addToast(
					[
						__('Failed to delete connection.', 'LION'),
						wpError.message,
					]
						.filter(Boolean)
						.join(' '),
					ToastType.Error
				)
			);
		}
	};

export function clearErrors(): Action {
	return {
		type: ActionType.ClearErrors,
	};
}

export function addToast(
	message: string,
	status: ToastType = ToastType.Info
): Action {
	return {
		type: ActionType.AddToast,
		toast: {
			id: Date.now().toString(),
			message,
			type: status,
		},
	};
}

export function removeToast(id: string): Action {
	return {
		type: ActionType.RemoveToast,
		toastId: id,
	};
}
