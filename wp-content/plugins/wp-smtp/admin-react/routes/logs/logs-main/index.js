/**
 * External dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, SearchControl } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { STORE_NAME as LogsStore } from '../../../data/src/logs/constants';
import LogsTable from '../logs-table';
import Pagination from '../pagination';
import { SearchBox } from '../styles';

/**
 * Component to display and manage logs.
 */
function LogsMain() {
	const [showSearchResetBtn, setShowSearchResetBtn] = useState(false);
	const [isSearching, setIsSearching] = useState(false);
	const [searchTerm, setSearchTerm] = useState('');

	const { totalPages, selectedLog, logs, currentPage } = useSelect(
		(select) => ({
			totalPages: select(LogsStore).getTotalPages(),
			selectedLog: select(LogsStore).getSelectedLog(),
			logs: select(LogsStore).getLogs(),
			currentPage: select(LogsStore).getCurrentPage(),
		}),
		[]
	);

	const { populateLogs, setCurrentPage, selectLog } = useDispatch(LogsStore);

	const onSearchCancel = () => {
		setShowSearchResetBtn(false);
		populateLogs(1);
	};

	const onSearchFormSubmitted = async () => {
		setIsSearching(true);
		setShowSearchResetBtn(true);
		await populateLogs(1, searchTerm);
		setIsSearching(false);
	};

	return (
		<div>
			<SearchBox>
				<SearchControl
					value={searchTerm}
					onSubmit={onSearchFormSubmitted}
					isSearching={isSearching}
					onChange={setSearchTerm}
					onKeyPress={(event) => {
						if (event.key === 'Enter') {
							onSearchFormSubmitted();
						}
					}}
				/>
				{showSearchResetBtn && (
					<Button
						onClick={onSearchCancel}
						icon={'dismiss'}
						type={'button'}
					>
						{__('Reset', 'LION')}
					</Button>
				)}
			</SearchBox>
			<LogsTable
				logs={logs}
				selectLog={selectLog}
				selectedLog={selectedLog}
			/>
			<Pagination
				totalPages={totalPages}
				currentPage={currentPage}
				setCurrentPage={setCurrentPage}
				searchTerm={showSearchResetBtn ? searchTerm : ''}
			/>
		</div>
	);
}

export default LogsMain;
