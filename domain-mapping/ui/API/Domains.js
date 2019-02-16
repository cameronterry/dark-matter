/**
 * We use jQuery's AJAX mechanism as this is already in WordPress and
 * doesn't require a separate dependency / library liks Axios ... for now.
 */

class Domains {
  /**
   * Handle adding a Domain and send a request to the REST API for the database
   * changes. If successful, then retrieve the new data set from the REST API.
   */
  add = ( data ) => {
    window.jQuery.ajax( {
			url : window.dmSettings.rest_root + 'dm/v1/domains',
      data : data,
      dataType : 'json',
			method : 'post',
			beforeSend : function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			},
			success : function () {
				this.getAll();
			}.bind( this )
		} );
  }

  /**
   * Handle deleting the Domain and send a request to the REST API for the
   * database changes. If successful, then retrieve the new data set from the
   * REST API.
   */
  async delete( domain ) {
    let result = null;

    try {
      result = await window.jQuery.ajax( {
        url : window.dmSettings.rest_root + 'dm/v1/domain/' + domain,
        dataType : 'json',
        method : 'DELETE',
        beforeSend : function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
        }
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
    let result = await window.jQuery.ajax( {
			url : window.dmSettings.rest_root + 'dm/v1/domains',
			dataType : 'json',
			method : 'GET',
			beforeSend : function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			}
    } );

    return result;
  }

  /**
   * Handle the update of the Domain and send a request to the REST API for the
   * database changes. If successful, then retrieve the new data set from the
   * REST API.
   */
  async update( data ) {
    data.force = true,

    delete data.site;

    let result = null;

    try {
      result = await window.jQuery.ajax( {
        url : window.dmSettings.rest_root + 'dm/v1/domain/' + data.domain,
        data : data,
        dataType : 'json',
        method : 'PUT',
        beforeSend : function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
        }
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
