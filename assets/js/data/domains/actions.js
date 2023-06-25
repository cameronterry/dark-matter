/* global dmp */

/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Action for removing a domain.
 *
 * @param {string} domain
 * @return {{domain, type: string}} Action object.
 */
export async function removeDomain( domain ) {
	try {
		const response = await apiFetch( {
			path: `${ dmp.endpoints.domain }/${ domain }`,
			method: 'DELETE',
		} );

		if ( response.deleted ) {
			return {
				type: 'REMOVE_DOMAIN',
				domain,
			};
		}

		return {
			type: 'PROCESS_ERROR',
			domain,
		};
	} catch ( error ) {
		return {
			type: 'API_ERROR',
			domain,
		};
	}
}

/**
 * Action for updating the domains state for a specific page.
 *
 * @param {Object}         pagination Page for which domain records are updated.
 * @param {Array.<object>} domains    Domain records.
 * @return {{pagination, domains, type: string}} Action object.
 */
export function setDomains( pagination, domains ) {
	return {
		type: 'SET_DOMAINS',
		domains,
		pagination,
	};
}

/**
 * Action for removing a domain.
 *
 * @param {string} domain Domain to update
 * @param {Object} data   Object
 * @return {{domain, type: string}} Action object.
 */
export async function updateDomain( domain, data ) {
	try {
		const response = await apiFetch( {
			path: `${ dmp.endpoints.domain }/${ domain }`,
			method: 'PUT',
			data,
		} );

		if ( ! response.error ) {
			return {
				type: 'UPDATE_DOMAIN',
				response,
			};
		}

		return {
			type: 'PROCESS_ERROR',
			domain,
		};
	} catch ( error ) {
		return {
			type: 'API_ERROR',
			domain,
		};
	}
}

/**
 * Action for fetching data from the REST API.
 *
 * @param {string} path Endpoint to retrieve data from.
 * @return {{path, type: string}} Action object.
 */
export function fetchFromAPI( path ) {
	return {
		type: 'FETCH_FROM_API',
		path,
	};
}
