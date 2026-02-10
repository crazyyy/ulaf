import { WpError } from './types';

export function convertToWpError(error: unknown): WpError {
	const isObject = typeof error === 'object' && error !== null;

	if (!isObject) {
		return {
			code: '',
			message: '',
			additional_errors: [],
		};
	}

	return {
		code:
			'code' in error && typeof error.code === 'string' ? error.code : '',
		message:
			'message' in error && typeof error.message === 'string'
				? error.message
				: '',
		additional_errors:
			'additional_errors' in error &&
			Array.isArray(error.additional_errors)
				? error.additional_errors
				: [],
	};
}
