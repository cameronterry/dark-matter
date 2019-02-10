import React from 'react';

class DomainDisplayPrimary extends React.Component {
  /**
   * Render component.
   */
  render() {
    const url = ( this.props.is_https ? 'https://' : 'http://' ) + this.props.domain;

    return (
      <td>
        <p>
          <strong><a href={ url }>{ this.props.domain }</a></strong>
        </p>
        <a href="#">Change to { this.props.is_https ? 'HTTP' : 'HTTPS' }</a> |
        <a href="#" className="submitdelete">Delete</a>
      </td>
    );
  }
}

export default DomainDisplayPrimary;
