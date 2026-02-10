/**
 * External dependencies
 */
import styled from "@emotion/styled";

/**
 * SolidWP dependencies
 */
import { Surface } from "@ithemes/ui";

export const StyledSurface = styled(Surface)`
    display: block;
    margin-bottom: ${({theme}) => theme.spacing.section};
`

export const StyledSurfaceHeader = styled.div`
	padding: ${ ( { theme } ) => theme.spacing.box };
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };

	p, span {
		margin-bottom: 5px;
	}
`