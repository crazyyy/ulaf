/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const FormControlLabel = styled.label`
	font-size: 16px;
	font-weight: 500;
	color: ${({ theme }) => theme.colors.text.normal};
	display: block;
`;
export const FormControl = styled.div`
	border-bottom: 1px solid ${({ theme }) => theme.colors.solidwp_mail.border};
	padding: ${({ theme }) => theme.spacing.box};
	display: grid;
	grid-template-columns: 1fr 6fr;
	&:last-child {
		border-bottom: none;
	}
	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		grid-template-columns: 1fr;
		gap: 10px;
	}
`;
export const FormControlInput = styled.div``;
