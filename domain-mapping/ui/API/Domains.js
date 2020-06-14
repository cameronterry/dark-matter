/**
 * We use jQuery's AJAX mechanism as this is already in WordPress and
 * doesn't require a separate dependency / library liks Axios ... for now.
 */

class Domains {
	/**
	 * Handle adding a Domain and send a request to the REST API for the database
	 * changes. If successful, then retrieve the new data set from the REST API.
	 *
	 * @param {Object} data Data record for the new domain.
	 */
	async add( data ) {
		let result = null;

		try {
			result = await window.jQuery.ajax( {
				url: window.dmSettings.rest_root + 'dm/v1/domain',
				data,
				dataType: 'json',
				method: 'post',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
				},
			} );
		} catch ( error ) {
			if ( error.responseJSON ) {
				result = error.responseJSON;
			}
		}

		return result;
	}

	/**
	 * Handle deleting the Domain and send a request to the REST API for the
	 * database changes. If successful, then retrieve the new data set from the
	 * REST API.
	 *
	 * @param {string} domain FQDN to be deleted.
	 */
	async delete( domain ) {
		let result = null;

		try {
			result = await window.jQuery.ajax( {
				url: window.dmSettings.rest_root + 'dm/v1/domain/' + domain,
				data: {
					force: true,
				},
				dataType: 'json',
				method: 'DELETE',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
				},
			} );
		} catch ( error ) {
			if ( error.responseJSON ) {
				result = error.responseJSON;
			}
		}

		return result;
	}

	/**
	 * Retrieve all the domains for a specific website.
	 */
	async getAll() {
		const result = await window.jQuery.ajax( {
			url: window.dmSettings.rest_root + 'dm/v1/domains',
			dataType: 'json',
			method: 'GET',
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			},
		} );

		return result;
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

		let result = null;

		try {
			result = await window.jQuery.ajax( {
				url:
					window.dmSettings.rest_root + 'dm/v1/domain/' + data.domain,
				data,
				dataType: 'json',
				method: 'PUT',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
				},
			} );
		} catch ( error ) {
			if ( error.responseJSON ) {
				result = error.responseJSON;
			}
		}

		return result;
	}
}

export default Domains;
