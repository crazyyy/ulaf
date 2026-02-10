/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';

export const LogItem = styled.div`
	padding: ${({ theme }) => theme.spacing.section};
	border-bottom: solid 1px ${({ theme }) => theme.colors.border.normal};
	cursor: pointer;
	border-left: solid 4px transparent;

	&:hover,
	&.active {
		background-color: ${({ theme }) => theme.colors.surface.secondary};
		border-left: solid 4px
			${({ theme }) => theme.colors.solidwp_mail.primary};
	}

	p {
		margin-bottom: 5px;
	}
`;

export const ErrorBadge = styled.span`
	display: inline-flex;
	padding: 0.25rem 0.5rem;
	border-radius: 1rem;
	background-color: ${({ theme }) => theme.colors.solidwp_mail.error};
	color: white;
`;

export const SenderEmail = styled.p`
	word-break: break-all;
	font-weight: 500;
`;

export const StyledFlex = styled(Flex)`
	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		flex-direction: column;
		align-items: flex-start;
	}
`;

export const ActionsBar = styled.div`
	margin-bottom: ${({ theme }) => theme.spacing.section};
	padding-left: calc(${({ theme }) => theme.spacing.section} + 4px);
`;
