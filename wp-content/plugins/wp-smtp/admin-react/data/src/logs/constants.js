/**
 * Default state for the store.
 *
 * @constant {Object}
 * @property {Array}       logs        - The array to store log entries.
 * @property {number}      totalPages  - The total number of pages of logs.
 * @property {number}      currentPage - The current page number.
 * @property {string}      searchTerm  - The current search term.
 * @property {Object|null} selectedLog - The currently selected log entry.
 */
export const DEFAULT_STATE = {
	logs: SolidWPMail.first_page_logs,
	totalPages: SolidWPMail.total_pages,
	currentPage: 1,
	selectedLog:
		SolidWPMail.first_page_logs.length > 0
			? SolidWPMail.first_page_logs[0]
			: null,
	isSearching: false,
};

/**
 * The name of the store.
 *
 * @constant {string}
 */
export const STORE_NAME = 'solidwp_mail/logs';
