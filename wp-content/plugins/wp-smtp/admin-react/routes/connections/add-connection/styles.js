/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { Link } from 'react-router-dom';

export const Container = styled.div`
	margin-top: ${({ theme }) => theme.spacing.section};
`;

export const StyledLink = styled(Link)`
	display: flex;
	margin-bottom: ${({ theme }) => theme.spacing.section};
	align-items: center;
	text-decoration: none;
	gap: 4px;
`;
