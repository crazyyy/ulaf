import styled from '@emotion/styled';

import { Card } from '@wordpress/components';

// we use this for define the inner tabs component
export const SettingForm = styled.form`
	margin-top: ${({ theme }) => theme.spacing.section};

	.solidwp-mail-settings-section-tabs {
		display: flex;
		align-items: start;
		flex-direction: row;
		gap: 20px;
		justify-content: space-between;

		.components-tab-panel__tabs {
			gap: 0.25rem;
			margin-top: 1.25rem;
			width: 10rem;

			.components-tab-panel__tabs-item {
				border: 2px solid transparent;
				border-radius: 1.25rem;
				box-shadow: none;
				font-size: 14px;
				font-weight: 600;
				height: auto;
				overflow-wrap: break-word;
				padding: 8px 16px;
				text-decoration: none;

				&.is-active {
					background: #e0e0e0;
					color: #333;
				}

				&:after {
					display: none;
				}
			}
		}

		.components-tab-panel__tab-content {
			display: flex;
			flex: 1 1 0%;
			flex-direction: column;
			gap: 20px;
			max-height: 100%;
			max-width: 100%;
			min-height: 0;
			min-width: 0;
		}
	}
`;
export const ExportCard = styled(Card)`
	margin-top: ${({ theme }) => theme.spacing.section};
`;
