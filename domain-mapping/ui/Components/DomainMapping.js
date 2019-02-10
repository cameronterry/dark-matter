import React from 'react';
import DomainRow from './DomainRow';

class DomainMapping extends React.Component {
  /**
   * Constructor.
   *
   * @param {object} props
   */
  constructor( props ) {
    super( props );

    this.state = {
      domains : []
    };
  }

  /**
   * Retrieve the domains for the Site from the REST API.
   */
  componentDidMount() {
    this.getData();
  }

  delete = ( domain ) => {
    window.jQuery.ajax( {
			url : window.dmSettings.rest_root + 'dm/v1/domain/' + domain,
			dataType : 'json',
			method : 'DELETE',
			beforeSend : function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			},
			success : function () {
				this.getData();
			}.bind( this )
		} );
  }

  getData() {
    /**
     * We use jQuery's AJAX mechanism as this is already in WordPress and
     * doesn't require a separate dependency / library liks Axios ... for now.
     */
    window.jQuery.ajax( {
			url : window.dmSettings.rest_root + 'dm/v1/domains',
			dataType : 'json',
			method : 'GET',
			beforeSend : function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			},
			success : function( data ) {
				this.setState( {
          domains: data
        } );
			}.bind( this )
		} );
  }

  /**
   * Render.
   */
  render() {
    const rows = [];

    this.state.domains.forEach( ( domain ) => {
      rows.push( <DomainRow key={ domain.id } delete={ this.delete } domain={ domain } update={ this.update } /> );
    } );

    return (
      <div className="wrap">
        <h1>Domains</h1>
        <table className="wp-list-table widefat fixed striped users">
          <thead>
            <tr>
              <th scope="col" className="manage-column">Domain</th>
              <th scope="col" className="manage-column">Is Primary?</th>
              <th scope="col" className="manage-column">Active?</th>
              <th scope="col" className="manage-column">Protocol</th>
            </tr>
          </thead>
          <tbody>
            {rows}
          </tbody>
        </table>
      </div>
    );
  }

  /**
   * Handle the update of the Domain and send a request to the REST API for the
   * database changes. If successful, then retrieve the new data set from the
   * REST API.
   */
  update = ( data ) => {
    data.force = true,

    delete data.site;

    window.jQuery.ajax( {
      url : window.dmSettings.rest_root + 'dm/v1/domain/' + data.domain,
      data : data,
			dataType : 'json',
			method : 'PUT',
			beforeSend : function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.dmSettings.nonce );
			},
			success : function () {
        console.log( this );
        this.getData();
			}.bind( this )
		} );
  }
}

export default DomainMapping;
