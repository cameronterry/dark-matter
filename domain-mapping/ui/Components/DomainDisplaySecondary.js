import React from 'react';

class DomainDisplaySecondary extends React.Component {
  handleDelete = () => {
    this.props.delete( this.props.data.domain );
  }

  handleProtocol = () => {
    let data = { ...this.props.data };

    data.is_https = ! data.is_https;

    this.props.update( data );
  }

  render() {
    const url = ( this.props.data.is_https ? 'https://' : 'http://' ) + this.props.data.domain;

    return (
      <td>
        <p>
          <a href={ url }>{ this.props.data.domain }</a>
        </p>
        <a href="#">Set as Primary</a> |
        <a href="#" onClick={ this.handleProtocol }>Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }</a> |
        <a href="#" className="submitdelete" onClick={ this.handleDelete }>Delete</a>
      </td>
    );
  }
}

export default DomainDisplaySecondary;
