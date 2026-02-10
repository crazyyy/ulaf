/**
 * External dependencies
 */
import { useNavigate } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { STORE_NAME as connectionsStore } from '../../../data/src/connections/constants';
import AmazonSes from '../../../routes/connections/connectors/amazon-ses';
import Brevo from '../../../routes/connections/connectors/brevo';
import Mailgun from '../../../routes/connections/connectors/mailgun';
import Postmark from '../../../routes/connections/connectors/postmark';
import Sendgrid from '../../../routes/connections/connectors/sendgrid';
import SmtpConnector from '../../../routes/connections/connectors/smtp-provider';

function ConnectionForm({ model, setModel }) {
	const navigate = useNavigate();

	const { errors, texts } = useSelect(
		(select) => ({
			errors: select(connectionsStore).getErrors(),
			texts: select(connectionsStore).getTexts(),
		}),
		[]
	);

	const { addConnection, updateConnection, clearErrors } =
		useDispatch(connectionsStore);

	useEffect(() => {
		// clear the error when this component loaded.
		clearErrors();
	}, [clearErrors]);

	async function handleFormPost(event) {
		event.preventDefault();
		if (model.id) {
			await updateConnection(model.id, model);
		} else {
			await addConnection(model);
		}

		navigate('/');
	}

	/**
	 * Handles the change in input fields for the SMTP provider form.
	 *
	 * @param {string} name  - The name of the input field.
	 * @param {string} value - The value of the input field.
	 */
	const handleInputChange = (name, value) => {
		setModel((prevProvider) => ({
			...prevProvider,
			[name]: value,
		}));
	};

	return (
		<form method={'post'} onSubmit={handleFormPost}>
			{'other' === model.name && (
				<SmtpConnector
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}
			{'brevo' === model.name && (
				<Brevo
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}
			{'mailgun' === model.name && (
				<Mailgun
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}
			{'sendgrid' === model.name && (
				<Sendgrid
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}
			{'amazon_ses' === model.name && (
				<AmazonSes
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}
			{'postmark' === model.name && (
				<Postmark
					model={model}
					handleInputChange={handleInputChange}
					errors={errors}
					texts={texts}
				/>
			)}

			<Flex justify={'end'} direction={'row'} gap={5}>
				<Button
					variant={'secondary'}
					type={'button'}
					onClick={() => navigate('/providers')}
				>
					{__('Back to Connections', 'LION')}
				</Button>
				<Button variant={'primary'} type={'submit'}>
					{__('Save Connection', 'LION')}
				</Button>
			</Flex>
		</form>
	);
}

export default ConnectionForm;
