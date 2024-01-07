import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch the data from the REST API using the specified path.
 *
 * @param {Object} action Reducer action.
 * @return {Promise<{totalItems: number, totalPages: number, body: *}>} Parsed JSON response from the REST API.
 * @class
 */
export async function FETCH_FROM_API( action ) {
	/**
	 * Skip the default parsing in `wp.apiFetch()` as we need access to the headers for pagination.
	 */
	const response = await apiFetch( {
		path: action.path,
		parse: false,
	} );

	return {
		body: await response.json(),
		totalItems: parseInt( response.headers.get( 'X-WP-Total' ), 10 ),
		totalPages: parseInt( response.headers.get( 'X-WP-TotalPages' ), 10 ),
	};
}
