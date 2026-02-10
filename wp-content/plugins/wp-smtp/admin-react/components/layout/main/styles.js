/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { NavLink } from 'react-router-dom';

export const SolidwpMailRoot = styled.div`
	padding: ${({ theme }) => theme.spacing.root};
	max-width: 1680px;
	margin: 0 auto;
	width: 100%;

	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		padding-left: 10px;
		padding-right: 10px;
	}
`;
export const Navigation = styled.nav`
	border-bottom: solid 1px ${({ theme }) => theme.colors.border.normal};
	display: flex;
	margin-top: ${({ theme }) => theme.spacing.section};
`;

export const StyledNavLink = styled(NavLink)`
	text-decoration: none;
	padding: 0.75rem 1.25rem;
	border-bottom: 4px solid transparent;

	&.active,
	&:hover {
		color: ${({ theme }) => theme.colors.solidwp_mail.primary};
		border-bottom: solid 4px
			${({ theme }) => theme.colors.solidwp_mail.primary};
	}
`;
