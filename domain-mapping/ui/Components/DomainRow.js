import React from 'react';
import DomainDisplayPrimary from './DomainDisplayPrimary';
import DomainDisplaySecondary from './DomainDisplaySecondary';

class DomainRow extends React.Component {
  /**
   * Handle the Activating / Deactivating of domains.
   */
  handleActivate = ( event ) => {
    event.preventDefault();

    let data = { ...this.props.domain };
    data.is_active = ! data.is_active;

    this.props.update( data );
  }

  /**
   * Handle the Deleting of the domain.
   */
  handleDelete = ( event ) => {
    event.preventDefault();

    let message = '';

    if ( this.props.domain.is_primary ) {
      message = 'Deleting the primary domain will stop any domain mapping for this Site. You will need to manually set another domain as primary. Do you wish to proceed?';
    } else {
      message = 'Are you sure you wish to delete ' + this.props.domain.domain + '?';
    }

    let confirm = window.confirm( message );

    if ( ! confirm ) {
      return;
    }

    this.props.delete( this.props.domain.domain );
  }

  /**
   * Handle the Setting the primary domain.
   */
  handlePrimary = ( event ) => {
    event.preventDefault();

    let confirm = window.confirm( 'Are you sure you wish to change ' + this.props.domain.domain + ' to be the primary domain? This will cause 301 redirects and may affect SEO.' );

    if ( ! confirm ) {
      return;
    }

    let data = { ...this.props.domain };
    data.is_primary = true;

    this.props.update( data );
  }

  /**
   * Handle the change in Protocol.
   */
  handleProtocol = ( event ) => {
    event.preventDefault();

    let data = { ...this.props.domain };
    let value = ! data.is_https;

    /**
     * We only want to get confirmation from the user if we are changing to
     * HTTPS.
     */
    if ( value ) {
      let confirm = window.confirm( 'Please ensure that your server configuration is setup properly before changing ' + this.props.domain.domain + ' to be HTTPS. Do you wish to proceed?' );

      if ( ! confirm ) {
        return;
      }
    }

    data.is_https = value;

    this.props.update( data );
  }

  /**
   * Render.
   */
  render() {
    return (
      <tr>
        { this.props.domain.is_primary ?
          <DomainDisplayPrimary data={ this.props.domain } activate={ this.handleActivate } protocol={ this.handleProtocol } delete={ this.handleDelete } />
          :
          <DomainDisplaySecondary data={ this.props.domain } activate={ this.handleActivate } primary={ this.handlePrimary } protocol={ this.handleProtocol } delete={ this.handleDelete } />
        }
        <td>{ ( this.props.domain.is_primary ? 'Yes' : 'No' ) }</td>
        <td>{ ( this.props.domain.is_active ? 'Yes' : 'No' ) }</td>
        <td>{ ( this.props.domain.is_https ? 'HTTPS' : 'HTTP' ) }</td>
      </tr>
    );
  }
}

export default DomainRow;
