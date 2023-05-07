/* global dmp */

import { addQueryArgs } from '@wordpress/url';

import { fetchFromAPI, setDomains } from './actions';

/**
 * Retrieve domains from the REST API.
 *
 * @param {number} page Page to retrieve.
 * @return {Object} Action object.
 */
export function* getDomains( page = 1 ) {
	const data = {
		page,
	};

	const path = addQueryArgs( dmp.endpoints.domains, data );
	const { body, totalItems, totalPages } = yield fetchFromAPI( path );

	return setDomains(
		{
			current: page,
			totalItems,
			totalPages,
		},
		body
	);
}
