/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Heading, Text, TextSize } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Header from '../header';

import { Navigation, SolidwpMailRoot, StyledNavLink } from './styles';

/** @typedef {import('React').ReactNode} ReactNode */

/**
 * Main layout component for the SolidWP Mail plugin.
 *
 * @param {Object}    props                 - The component props.
 * @param {ReactNode} props.children        - The children components to render within the layout.
 * @param {string}    [props.headerText=''] - The text to display in the header.
 * @param {boolean}   [props.withNav=true]  - Whether to display the navigation.
 */
const MainLayout = ({ children, headerText = '', withNav = true }) => {
	return (
		<>
			<Header />
			<SolidwpMailRoot>
				<Heading level={2}>{headerText}</Heading>
				{withNav && (
					<Navigation>
						<StyledNavLink to="/providers">
							<Text
								size={TextSize.LARGE}
								weight={600}
								align={'center'}
							>
								{__('Connections', 'LION')}
							</Text>
						</StyledNavLink>
						<StyledNavLink to="/email-test">
							<Text
								size={TextSize.LARGE}
								weight={600}
								align={'center'}
							>
								{__('Email Test', 'LION')}
							</Text>
						</StyledNavLink>
					</Navigation>
				)}
				{children}
			</SolidwpMailRoot>
		</>
	);
};

export default MainLayout;
