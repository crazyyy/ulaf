/**
 * SolidWP dependencies
 */
import { solidTheme } from '@ithemes/ui';

export const solidMailTheme = {
	...solidTheme,
	spacing: {
		section: '1.25rem',
		box: '2rem',
		root: '1.25rem 1.25rem 4rem 1.5rem',
		empty_connections: '80px 0',
	},
	colors: {
		...solidTheme.colors,
		solidwp_mail: {
			primary: '#6817C5',
			border: '#ccc',
			error: '#f44336',
		},
	},
};
