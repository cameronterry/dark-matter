/* global dmp */

/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Action for updating a domain.
 *
 * @param {Object}  data            New domain record to be added.
 * @param {string}  data.domain     Domain, without the protocol.
 * @param {boolean} data.is_primary Whether the domain is primary or secondary.
 * @param {boolean} data.is_active  Is Active or not Active.
 * @param {boolean} data.is_https   Use HTTP or HTTPS.
 * @param {string}  data.type       Domain type: "main" or "media".
 * @param {boolean} throwOnError    Throw an error. Defaults to `false`.
 * @return {*} Test.
 */
export const addDomain = ( data, throwOnError = false ) => async ( { dispatch } ) => {
	let error;
	let newDomain = false;

	try {
		newDomain = await apiFetch( {
			path: `${ dmp.endpoints.domain }`,
			method: 'POST',
			data,
		} );

		if ( ! newDomain.error ) {
			dispatch( {
				type: 'ADD_DOMAIN',
				domain: newDomain,
			} );

			return newDomain;
		}
	} catch ( _error ) {
		error = _error;
	}

	if ( error && throwOnError ) {
		throw error;
	}

	return newDomain;
};

/**
 * Action for removing a domain.
 *
 * @param {string}  domain
 * @param {boolean} force
 * @return {{domain, type: string}} Action object.
 */
export async function removeDomain( domain, force = false ) {
	try {
		const response = await apiFetch( {
			path: `${ dmp.endpoints.domain }/${ domain }`,
			method: 'DELETE',
			data: {
				force,
			},
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
 * Action for updating a domain.
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
