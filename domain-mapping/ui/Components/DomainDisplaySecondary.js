import React from 'react';

class DomainDisplaySecondary extends React.Component {
  render() {
    const url = ( this.props.is_https ? 'https://' : 'http://' ) + this.props.domain;

    return (
      <td>
        <p>
          <a href={ url }>{ this.props.domain }</a>
        </p>
        <a href="#">Set as Primary</a> |
        <a href="#">Change to { this.props.is_https ? 'HTTP' : 'HTTPS' }</a> |
        <a href="#" className="submitdelete">Delete</a>
      </td>
    );
  }
}

export default DomainDisplaySecondary;
