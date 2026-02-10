/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const Toolbar = styled.div`
	width: 100%;
	padding: 10px 24px;
	background: #fff;
	box-sizing: border-box;

	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		padding-left: 10px;
		padding-right: 10px;
	}
`;
