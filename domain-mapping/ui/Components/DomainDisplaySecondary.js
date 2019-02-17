import React from 'react';

class DomainDisplaySecondary extends React.Component {
  /**
   * Handle the Deleting of the domain.
   */
  handleDelete = ( event ) => {
    event.preventDefault();

    let confirm = window.confirm( 'Are you sure you wish to delete ' + this.props.data.domain + '?' );

    if ( ! confirm ) {
      return;
    }

    this.props.delete( this.props.data.domain );
  }

  /**
   * Handle the Setting the primary domain.
   */
  handlePrimary = ( event ) => {
    event.preventDefault();

    let confirm = window.confirm( 'Are you sure you wish to change ' + this.props.data.domain + ' to be the primary domain? This will cause 301 redirects and may affect SEO.' );

    if ( ! confirm ) {
      return;
    }

    let data = { ...this.props.data };
    data.is_primary = true;

    this.props.update( data );
  }

  /**
   * Handle the change in Protocol.
   */
  handleProtocol = ( event ) => {
    event.preventDefault();

    let data = { ...this.props.data };
    let value = ! data.is_https;

    /**
     * We only want to get confirmation from the user if we are changing to
     * HTTPS.
     */
    if ( value ) {
      let confirm = window.confirm( 'Please ensure that your server configuration is setup properly before changing ' + this.props.data.domain + ' to be HTTPS. Do you wish to proceed?' );

      if ( ! confirm ) {
        return;
      }
    }

    data.is_https = value;

    this.props.update( data );
  }

  /**
   * Render component.
   */
  render() {
    const url = ( this.props.data.is_https ? 'https://' : 'http://' ) + this.props.data.domain;

    return (
      <td className="domain-options">
        <p>
          <a href={ url }>{ this.props.data.domain }</a>
        </p>
        <a href="#" onClick={ this.handlePrimary }>Set as Primary</a>
        <span>|</span>
        <a href="#" onClick={ this.handleProtocol }>Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }</a>
        <span>|</span>
        <a href="#" className="submitdelete" onClick={ this.handleDelete }>Delete</a>
      </td>
    );
  }
}

export default DomainDisplaySecondary;
