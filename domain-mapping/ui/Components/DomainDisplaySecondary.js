import React from 'react';

class DomainDisplaySecondary extends React.Component {
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
        <a href="#" onClick={ this.props.primary }>Set as Primary</a>
        <span>|</span>
        <a href="#" onClick={ this.props.protocol }>Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }</a>
        <span>|</span>
        <a href="#" className="submitdelete" onClick={ this.props.delete }>Delete</a>
      </td>
    );
  }
}

export default DomainDisplaySecondary;
