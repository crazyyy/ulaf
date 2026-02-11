/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Notice, Root } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import '../../data/src/logs/index';
import MainLayout from '../../components/layout/main';
import { solidMailTheme } from '../../components/layout/theme';
import { STORE_NAME as LogsStore } from '../../data/src/logs/constants';
import LogsMain from '../logs/logs-main';

import LogDetail from './log-detail';
import { Container, StyledDetailContainer } from './styles';

/**
 * Component for displaying and managing email logs.
 *
 * @return {JSX.Element} The rendered Logs component.
 */
function Logs() {
	const { logs, isSearching } = useSelect(
		(select) => ({
			logs: select(LogsStore).getLogs(),
			isSearching: select(LogsStore).isSearching(),
		}),
		[]
	);

	return (
		<Root theme={solidMailTheme}>
			<MainLayout headerText={__('Email Logs', 'LION')} withNav={false}>
				<Container>
					{logs.length === 0 && isSearching === false && (
						<Notice
							text={__('No logs found.', 'LION')}
							type={'info'}
						/>
					)}
					{(logs.length > 0 ||
						(logs.length === 0 && isSearching === true)) && (
						<>
							<LogsMain />
							<StyledDetailContainer>
								<LogDetail />
							</StyledDetailContainer>
						</>
					)}
				</Container>
			</MainLayout>
		</Root>
	);
}

export default Logs;
