import React from 'react';

class DomainDisplayPrimary extends React.Component {
  /**
   * Render component.
   */
  render() {
    const url = ( this.props.data.is_https ? 'https://' : 'http://' ) + this.props.data.domain;

    return (
      <td className="domain-options">
        <p>
          <strong><a href={ url }>{ this.props.data.domain }</a></strong>
        </p>
        { this.props.data.is_active ?
          <a href="#" onClick={ this.props.activate }>Deactivate</a>
            :
            <a href="#" onClick={ this.props.activate }>Activate</a>
        }
        <span>|</span>
        <a href="#" onClick={ this.props.protocol }>Change to { this.props.data.is_https ? 'HTTP' : 'HTTPS' }</a>
        <span>|</span>
        <a href="#" className="submitdelete" onClick={ this.props.delete }>Delete</a>
      </td>
    );
  }
}

export default DomainDisplayPrimary;
