/**
 * WordPress dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Text, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledSurface, StyledSurfaceHeader } from '../../../../assets/common';
import { FormTextInput } from '../../../../components/form';

function MailgunConnector({ model, handleInputChange, errors, texts }) {
	return (
		<>
			<StyledSurface>
				<StyledSurfaceHeader>
					<Text weight={500} as={'p'} size={TextSize.LARGE}>
						{__('Sender Setting', 'LION')}
					</Text>
					<Text>{texts.sender_heading_text}</Text>
				</StyledSurfaceHeader>
				<FormTextInput
					label={__('From email', 'LION')}
					name="from_email"
					type="email"
					value={model.from_email}
					error={errors.from_email}
					onChange={(value) => {
						handleInputChange('from_email', value);
					}}
					help={texts.from_email}
				/>
				<FormTextInput
					label={__('From name', 'LION')}
					name="from_name"
					value={model.from_name}
					error={errors.from_name}
					onChange={(value) => {
						handleInputChange('from_name', value);
					}}
					help={texts.from_name}
				/>
			</StyledSurface>
			<StyledSurface>
				<StyledSurfaceHeader>
					<Text weight={500} as={'p'} size={TextSize.LARGE}>
						{__('SMTP Settings', 'LION')}
					</Text>
					<Text>
						{createInterpolateElement(
							__(
								'Mailgun is a comprehensive email automation platform with APIs for sending, receiving, and tracking emails. For pricing information <pricing>visit here</pricing>. For configuration information, <help>visit here</help>.',
								'LION'
							),
							{
								pricing: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content
									<a
										href="https://go.solidwp.com/mail-mailgun-pricing"
										rel="noreferrer"
										target="_blank"
									/>
								),
								help: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content
									<a
										href="https://go.solidwp.com/mail-mailgun-config"
										rel="noreferrer"
										target="_blank"
									/>
								),
							}
						)}
					</Text>
				</StyledSurfaceHeader>

				<FormTextInput
					label={__('Username', 'LION')}
					name="smtp_username"
					type="text"
					value={model.smtp_username}
					error={errors.smtp_username}
					onChange={(value) => {
						handleInputChange('smtp_username', value);
					}}
					help={texts.smtp_username}
				/>
				<FormTextInput
					label={__('Password', 'LION')}
					name="smtp_password"
					type="password"
					value={model.smtp_password}
					error={errors.smtp_password}
					onChange={(value) => {
						handleInputChange('smtp_password', value);
					}}
					help={texts.smtp_password}
				/>
			</StyledSurface>
		</>
	);
}

export default MailgunConnector;
