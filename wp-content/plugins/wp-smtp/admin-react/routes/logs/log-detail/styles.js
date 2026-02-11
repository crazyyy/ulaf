/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Notice, Surface } from '@ithemes/ui';

export const Header = styled.div`
	padding: ${({ theme }) => theme.spacing.section};
	border-bottom: solid 1px ${({ theme }) => theme.colors.border.normal};
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;
export const HeaderRow = styled.div`
	display: flex;
	justify-content: space-between;
	gap: 20px;
`;

export const Body = styled.div`
	padding: ${({ theme }) => theme.spacing.section};

	p {
		margin-bottom: 10px;
	}
`;

export const Empty = styled.div`
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	width: 100%;
`;

export const StyledSurface = styled(Surface)`
	margin-bottom: ${({ theme }) => theme.spacing.section};
	max-height: 800px;
	overflow-y: scroll;
`;
export const StyledNotice = styled(Notice)`
	margin: ${({ theme }) => theme.spacing.section};
	margin-bottom: 0;
`;
