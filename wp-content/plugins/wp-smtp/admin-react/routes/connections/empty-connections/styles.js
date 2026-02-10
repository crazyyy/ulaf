/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Callout } from '@ithemes/ui';

export const StyledCallout = styled(Callout)`
	padding: ${({ theme }) => theme.spacing.empty_connections};

	span {
		margin-top: ${({ theme }) => theme.spacing.section};
		margin-bottom: ${({ theme }) => theme.spacing.section};
	}
`;

export const StyledCalloutItem = styled.div`
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
`;
