/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Text, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledSurface, StyledSurfaceHeader } from '../../../../assets/common';
import {
	FormTextInput,
	FormRadioGroup,
	FormToggle,
} from '../../../../components/form';

/**
 * SmtpProvider Component
 *
 * This component renders the form fields required to configure an SMTP provider.
 * It includes fields for the sender settings and SMTP settings, handling both
 * authenticated and non-authenticated SMTP configurations.
 *
 * @param {Object}   props                   - The component props.
 * @param {Object}   props.model             - The model object containing SMTP settings.
 * @param {Function} props.handleInputChange - The function to handle input changes.
 * @param {Object}   props.errors            - An object containing error messages for each field.
 * @param {Object}   props.texts             - The Texts.
 */
function SmtpConnector({ model, handleInputChange, errors, texts }) {
	const [auth, setAuth] = useState(model.smtp_auth === 'yes');

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
					onChange={(value) => handleInputChange('from_email', value)}
					help={texts.from_email}
				/>
				<FormTextInput
					label={__('From name', 'LION')}
					name="from_name"
					value={model.from_name}
					error={errors.from_name}
					onChange={(value) => handleInputChange('from_name', value)}
					help={texts.from_name}
				/>
			</StyledSurface>
			<StyledSurface>
				<StyledSurfaceHeader>
					<Text weight={500} as={'p'} size={TextSize.LARGE}>
						{__('SMTP Settings', 'LION')}
					</Text>
					<Text>
						{__(
							'SMTP (Simple Mail Transfer Protocol) settings are configurations needed to send emails from an email client or application',
							'LION'
						)}
					</Text>
				</StyledSurfaceHeader>
				<FormTextInput
					label={__('SMTP Host', 'LION')}
					name="smtp_host"
					type="text"
					value={model.smtp_host}
					error={errors.smtp_host}
					onChange={(value) => handleInputChange('smtp_host', value)}
					help={texts.smtp_host}
				/>
				<FormTextInput
					label={__('SMTP Port', 'LION')}
					name="smtp_port"
					type="text"
					value={model.smtp_port}
					error={errors.smtp_port}
					onChange={(value) => handleInputChange('smtp_port', value)}
					help={texts.smtp_port}
				/>
				<FormRadioGroup
					label={__('Secure', 'LION')}
					name="smtp_secure"
					options={[
						{ value: '', label: __('None', 'LION') },
						{ value: 'ssl', label: __('SSL', 'LION') },
						{ value: 'tls', label: __('TLS', 'LION') },
					]}
					onChange={(value) =>
						handleInputChange('smtp_secure', value)
					}
					value={model.smtp_secure}
					error={errors.smtp_secure}
					help={texts.smtp_secure}
				/>
				<FormToggle
					label={__('SMTP Authentication', 'LION')}
					name="smtp_auth"
					value={auth}
					error={errors.smtp_auth}
					onChange={(value) => {
						setAuth(value);
						handleInputChange(
							'smtp_auth',
							value === true ? 'yes' : 'no'
						);
					}}
					help={texts.smtp_auth}
				/>
				{auth && (
					<>
						<FormTextInput
							label={__('Username', 'LION')}
							name="smtp_username"
							type="text"
							value={model.smtp_username}
							error={errors.smtp_username}
							onChange={(value) =>
								handleInputChange('smtp_username', value)
							}
							help={texts.smtp_username}
						/>
						<FormTextInput
							label={__('Password', 'LION')}
							name="smtp_password"
							type="password"
							value={model.smtp_password}
							error={errors.smtp_password}
							onChange={(value) =>
								handleInputChange('smtp_password', value)
							}
							help={texts.smtp_password}
						/>
					</>
				)}
			</StyledSurface>
		</>
	);
}

export default SmtpConnector;
