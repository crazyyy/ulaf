/**
 * External dependencies
 */
import { useNavigate } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Logo } from '../../../components/icons';

import { StyledCallout, StyledCalloutItem } from './styles';

const Empty = () => {
	const navigate = useNavigate();

	const helpText = createInterpolateElement(
		__(
			"Looks like you haven't set up any email providers yet. Let's get started! If you need assistance, here is the <help>documentation link</help>.",
			'LION'
		),
		{
			help: (
				// eslint-disable-next-line jsx-a11y/anchor-has-content
				<a
					href="https://go.solidwp.com/mail-set-up-connection"
					rel="noreferrer"
					target="_blank"
				/>
			),
		}
	);

	return (
		<StyledCallout>
			<StyledCalloutItem>
				<Logo />

				<Text text={helpText} />
				<Button
					variant={'primary'}
					icon={'plus'}
					text={__('Add Connection', 'LION')}
					onClick={() => navigate('add')}
				/>
			</StyledCalloutItem>
		</StyledCallout>
	);
};

export default Empty;
