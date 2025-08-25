/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

class Domains {
	/**
	 * Handle adding a Domain and send a request to the REST API for the database
	 * changes. If successful, then retrieve the new data set from the REST API.
	 *
	 * @param {Object} data Data record for the new domain.
	 */
	async add( data ) {
		try {
			return await apiFetch( {
				data,
				method: 'POST',
				path: '/dm/v1/domain',
			} );
		} catch ( error ) {
			return error;
		}
	}

	/**
	 * Handle deleting the Domain and send a request to the REST API for the
	 * database changes. If successful, then retrieve the new data set from the
	 * REST API.
	 *
	 * @param {string} domain FQDN to be deleted.
	 */
	async delete( domain ) {
		return await apiFetch( {
			data: {
				force: true,
			},
			method: 'DELETE',
			path: `/dm/v1/domain/${domain}`,
		} );
	}

	/**
	 * Retrieve all the domains for a specific website.
	 */
	async getAll() {
		return await apiFetch( {
			path: '/dm/v1/domains',
		} );
	}

	/**
	 * Handle the update of the Domain and send a request to the REST API for the
	 * database changes. If successful, then retrieve the new data set from the
	 * REST API.
	 *
	 * @param {Object} data Data record for the domain to be updated.
	 */
	async update( data ) {
		/**
		 * Set the force attribute to true.
		 */
		data.force = true;

		/**
		 * Remove the site property.
		 */
		delete data.site;

		return await apiFetch( {
			data,
			method: 'PUT',
			path: `/dm/v1/domain/${data.domain}`,
		} );
	}
}

export default Domains;
