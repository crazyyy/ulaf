/**
 * Selectors to access store state.
 */
const selectors = {
	/**
	 * Get all logs from the state.
	 *
	 * @param {Object} state - The current state.
	 * @return {Array} The array of logs.
	 */
	getLogs(state) {
		return state.logs;
	},
	/**
	 * Get the total number of pages from the state.
	 *
	 * @param {Object} state - The current state.
	 * @return {number} The total number of pages.
	 */
	getTotalPages(state) {
		return state.totalPages;
	},
	/**
	 * Get the selected log from the state.
	 *
	 * @param {Object} state - The current state.
	 * @return {Object|null} The selected log.
	 */
	getSelectedLog(state) {
		return state.selectedLog;
	},
	/**
	 * Get the current page from the state.
	 *
	 * @param {Object} state - The current state.
	 * @return {number} The current page number.
	 */
	getCurrentPage(state) {
		return state.currentPage;
	},
	/**
	 * Check if searching is in progress from the state.
	 *
	 * @param {Object} state - The current state.
	 * @return {boolean} True if searching is in progress, false otherwise.
	 */
	isSearching(state) {
		return state.isSearching;
	},
};

export default selectors;
