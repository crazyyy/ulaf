/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';

export const StyledModel = styled(Modal)`
	.components-modal__header {
		border-color: ${({ theme }) => theme.colors.border.normal};
	}
`;

export const StyledBody = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: ${({ theme: { getSize } }) => getSize(1.5)};
	padding-top: ${({ theme: { getSize } }) => getSize(1.5)};
`;

export const StyledActions = styled.div`
	display: flex;
	gap: ${({ theme: { getSize } }) => getSize(0.5)};
`;
