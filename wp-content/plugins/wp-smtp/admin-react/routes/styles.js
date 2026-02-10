/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { SnackbarList } from '@wordpress/components';

export const FloatingSnackBar = styled(SnackbarList)`
	position: fixed;
	right: 20px;
	bottom: 20px;
	display: flex;
	flex-direction: column;
	align-items: flex-end;
`;
