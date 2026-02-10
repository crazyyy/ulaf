/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const ConnectionInfo = styled.div`
	display: flex;

	@media (min-width: ${({ theme }) => `${theme.breaks.small}px`}) {
		grid-column: 1 / 4;
	}

	@media (min-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		grid-column: unset;
	}
`;

export const ConnectionInfoImage = styled.div`
	width: 40px;
	height: 40px;
	border-radius: 4px;
	margin-right: 20px;

	@media (max-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		display: none;
	}
`;

export const ConnectionInfoName = styled.div`
	span {
		display: block;
		margin-top: 5px;
	}
`;

export const ConnectionToggle = styled.div`
	& .components-base-control {
		margin-bottom: 0 !important;
	}

	@media (min-width: ${({ theme }) => `${theme.breaks.medium}px`}) {
		& .components-toggle-control__label {
			display: none;
		}
	}
`;

export const ConnectionActions = styled.div`
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
`;
