/**
 * External dependencies
 */
import { stringify } from 'qs';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { Flex, FlexItem } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledSurface } from '../../assets/common';
import { FormTextInput, FormTextarea } from '../../components/form';
import { STORE_NAME as connectionStore } from '../../data/src/connections/constants';

import { Container } from './styles';

/**
 * Component for sending a test email.
 *
 * @return {JSX.Element} The rendered EmailTest component.
 */
const EmailTest = () => {
	const [fromEmail, setFromEmail] = useState('');
	const [toEmail, setToEmail] = useState('');
	const [subject, setSubject] = useState('');
	const [message, setMessage] = useState('');
	const [loading, setLoading] = useState(false);
	const [errors, setErrors] = useState([]);

	const { addToast } = useDispatch(connectionStore);

	const handleSendTestEmail = async (event) => {
		event.preventDefault();
		try {
			setLoading(true);
			// const response = await sendTestEmail( toEmail, subject, message );
			const response = await apiFetch({
				url: addQueryArgs(ajaxurl, {
					action: 'solidwp_mail_send_test_email',
					solidwp_mail_connections_nonce:
						SolidWPMail.nonces.send_test_email,
				}),
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: stringify({
					from_email: fromEmail,
					to_email: toEmail,
					subject,
					message,
				}),
			});
			if (response.success === false && response.data.validation) {
				setErrors(response.data.validation);
				addToast(
					__(
						'Validation failed. Please check the highlighted fields and try again.',
						'LION'
					)
				);
			} else {
				addToast(response.data.message);
			}
			setLoading(false);
		} catch (_error) {
			addToast(__('Error sending test email', 'LION'));
		}
	};

	return (
		<Container>
			<form method="post" onSubmit={handleSendTestEmail}>
				<StyledSurface>
					<FormTextInput
						label={__('From Email (optional)', 'LION')}
						value={fromEmail}
						error={errors.from_email}
						onChange={setFromEmail}
						help={__(
							'Enter the address emails are sent from. Remember, this may determine which connection is used.',
							'LION'
						)}
					/>
					<FormTextInput
						label={__('To Email', 'LION')}
						value={toEmail}
						error={errors.to_email}
						onChange={setToEmail}
						help={__("Enter the recipient's email address", 'LION')}
					/>
					<FormTextInput
						label={__('Subject', 'LION')}
						value={subject}
						error={errors.subject}
						onChange={setSubject}
						help={__(
							'Provide the subject of the test email.',
							'LION'
						)}
					/>
					<FormTextarea
						label={__('Message', 'LION')}
						value={message}
						error={errors.message}
						onChange={setMessage}
						help={__('Enter the email message', 'LION')}
					/>
				</StyledSurface>
				<Flex align={'flex-end'} direction={'column'}>
					<FlexItem>
						<Button
							disabled={loading}
							isBusy={loading}
							variant={'primary'}
							type={'submit'}
						>
							{__('Send Test Email', 'LION')}
						</Button>
					</FlexItem>
				</Flex>
			</form>
		</Container>
	);
};

export default EmailTest;
