module.exports = {
	root: true,
	parser: '@typescript-eslint/parser',
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended',
		'plugin:@typescript-eslint/recommended',
		'plugin:import/recommended',
		'plugin:import/typescript',
	],
	plugins: ['@typescript-eslint', 'import'],
	settings: {
		jsdoc: {
			mode: 'typescript',
		},
		'import/resolver': {
			typescript: {
				alwaysTryTypes: true,
			},
			node: {
				extensions: ['.js', '.jsx', '.ts', '.tsx'],
			},
		},
	},
	rules: {
		'@wordpress/i18n-text-domain': [
			'error',
			{
				allowedTextDomain: ['LION'],
			},
		],
		'@typescript-eslint/no-explicit-any': 'warn',
		'@typescript-eslint/explicit-function-return-type': 'off',
		'@typescript-eslint/explicit-module-boundary-types': 'off',
		'@typescript-eslint/no-unused-vars': [
			'error',
			{ argsIgnorePattern: '^_', caughtErrorsIgnorePattern: '^_' },
		],
		'import/no-unresolved': 'error',
		'import/extensions': [
			'error',
			'ignorePackages',
			{
				js: 'never',
				jsx: 'never',
				ts: 'never',
				tsx: 'never',
			},
		],
		'import/order': [
			'error',
			{
				alphabetize: {
					order: 'asc',
					caseInsensitive: true,
				},
				'newlines-between': 'always',
				groups: [
					'builtin',
					'external',
					'internal',
					'parent',
					'sibling',
					'index',
				],
				pathGroups: [
					{
						pattern: '@wordpress/**',
						group: 'external',
						position: 'after',
					},
					{
						pattern: '@ithemes/**',
						group: 'internal',
						position: 'before',
					},
				],
				pathGroupsExcludedImportTypes: [
					'builtin',
					'react',
					'react-dom',
					'react-router-dom',
				],
			},
		],
	},
	globals: {
		SolidWPMail: true,
		ajaxurl: true,
		solidMailSettings: true,
	},
	overrides: [
		{
			files: ['*.ts', '*.tsx'],
			parserOptions: {
				project: ['./tsconfig.json'],
			},
		},
	],
};
