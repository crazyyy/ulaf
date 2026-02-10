/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Text, TextSize } from '@ithemes/ui';

/**
 * Pagination component for navigating through pages of logs.
 *
 * @param {Object}   props                - The component props.
 * @param {number}   props.currentPage    - The current page number.
 * @param {number}   props.totalPages     - The total number of pages.
 * @param {Function} props.setCurrentPage - The function to set the current page.
 * @param {string}   props.searchTerm     - The current search term.
 *
 * @return {JSX.Element} The rendered Pagination component.
 */
function Pagination({ currentPage, totalPages, searchTerm, setCurrentPage }) {
	/**
	 * Handles the click event for the previous page button.
	 */
	const handlePrevPage = () => {
		if (currentPage > 0) {
			setCurrentPage(currentPage - 1, searchTerm);
		}
	};

	/**
	 * Handles the click event for the next page button.
	 */
	const handleNextPage = () => {
		if (currentPage < totalPages) {
			setCurrentPage(currentPage + 1, searchTerm);
		}
	};

	return (
		<Flex justify={'space-between'}>
			<Button
				variant={'primary'}
				icon={'arrow-left'}
				onClick={handlePrevPage}
				disabled={currentPage <= 1}
			>
				{__('Prev', 'LION')}
			</Button>
			<Text size={TextSize.LARGE}>
				{currentPage} {__('of', 'LION')} {totalPages}
			</Text>
			<Button
				variant={'primary'}
				icon={'arrow-right'}
				onClick={handleNextPage}
				disabled={currentPage >= totalPages}
			>
				{__('Next', 'LION')}
			</Button>
		</Flex>
	);
}

export default Pagination;
