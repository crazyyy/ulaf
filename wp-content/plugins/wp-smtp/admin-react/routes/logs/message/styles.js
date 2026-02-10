/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledMessageTab = styled(Surface)``;

export const StyledRawMessage = styled.pre`
	margin: 0;
	overflow-x: auto;
	padding: ${({ theme }) => theme.spacing.section};
`;
