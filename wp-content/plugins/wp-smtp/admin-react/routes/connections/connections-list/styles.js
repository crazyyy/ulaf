/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

export const ConnectionsTable = styled.div``;

export const ConnectionsTableRow = styled.div`
	display: ${({ hideOnSmall }) => (hideOnSmall ? 'none' : 'grid')};
	grid-template-columns: 1fr;
	gap: 1rem;
	align-items: center;
	padding: 20px 32px;
	border-bottom: 1px solid ${({ theme }) => theme.colors.border.normal};

	@media (min-width: ${({ theme }) => `${theme.breaks.small}px`}) {
		grid-template-columns: 2fr 1fr 2fr;
	}

	@media (min-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		display: grid;
		grid-template-columns: 3fr repeat(2, 1fr) 2fr;
	}
`;

export const StyledFlex = styled(Flex)`
	margin-bottom: ${({ theme }) => theme.spacing.section};
`;

export const StyledText = styled(Text)`
	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		display: ${({ hideOnSmall }) => (hideOnSmall ? 'none' : '')};
	}
`;
