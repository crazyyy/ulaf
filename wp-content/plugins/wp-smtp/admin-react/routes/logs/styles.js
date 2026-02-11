/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const Container = styled.div`
	margin-top: ${({ theme }) => theme.spacing.section};
	display: grid;
	grid-template-columns: 4fr 6fr;
	gap: 20px;

	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		grid-template-columns: 1fr;
		gap: 20px;
	}
`;

export const StyledSurface = styled(Surface)`
	margin-bottom: ${({ theme }) => theme.spacing.section};
`;

export const SearchBox = styled.div`
	margin-bottom: ${({ theme }) => theme.spacing.section};
	display: grid;
	grid-template-columns: 6fr 1fr;
	gap: 10px;
`;

export const StyledDetailContainer = styled(Surface)`
	overflow-x: hidden;
`;
