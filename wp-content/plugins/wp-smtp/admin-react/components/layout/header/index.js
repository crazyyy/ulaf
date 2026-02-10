/**
 * Internal dependencies
 */
import { LogoHeader } from '../../../components/icons';

import { Toolbar } from './styles';

/**
 * Header component displaying the toolbar with a logo.
 */
const Header = () => {
	return (
		<Toolbar>
			<LogoHeader />
		</Toolbar>
	);
};

export default Header;
