import React from 'react';

class DomainDisplaySecondary extends React.Component {
  /**
   * Handle the Deleting of the domain.
   */
  handleDelete = ( event ) => {
    event.preventDefault();

    this.props.delete( this.props.data.domain );
  }

  handlePrimary = ( event ) => {
    event.preventDefault();

    let data = { ...this.props.data };
    data.is_primary = true;

    this.props.update( data );
  }

  /**
   * Handle the change in Protocol.
   */
  handleProtocol = () => {
    event.preventDefault();

    let data = { ...this.props.data };
    data.is_https = ! data.is_https;

    this.props.update( data );
  }

  /**
   * Render component.
   */
  render() {
    const url = ( this.props.data.is_https ? 'https://' : 'http://' ) + this.props.data.domain;

    return (
      <td>
        <p>
          <a href={ url }>{ this.props.data.domain }</a>
        </p>
        <a href="#" onClick={ this.handlePrimary }>Set as Primary</a> |
        <a href="#" onClick={ this.handleProtocol }>Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }</a> |
        <a href="#" className="submitdelete" onClick={ this.handleDelete }>Delete</a>
      </td>
    );
  }
}

export default DomainDisplaySecondary;
