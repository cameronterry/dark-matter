/**
 * Retrieve the Domains for the current store.
 *
 * @param {Object} state Current state of the Domains store.
 * @return {Array.<object>} Array of domain records.
 */
export function getDomains( state ) {
	return state.domains;
}

/**
 * Retrieve the current page in view.
 *
 * @param {Object} state Current state of the Domains store.
 * @return {Object} Pagination data.
 */
export function getPagination( state ) {
	return state.pagination;
}
