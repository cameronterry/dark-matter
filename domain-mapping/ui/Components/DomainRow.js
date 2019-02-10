import React from 'react';
import DomainDisplayPrimary from './DomainDisplayPrimary';
import DomainDisplaySecondary from './DomainDisplaySecondary';

class DomainRow extends React.Component {
  /**
   * Render.
   */
  render() {
    return (
      <tr>
        { this.props.domain.is_primary ?
          <DomainDisplayPrimary domain={ this.props.domain.domain } is_https={ this.props.domain.is_https } />
          :
          <DomainDisplaySecondary domain={ this.props.domain.domain } is_https={ this.props.domain.is_https } />
        }
        <td>{ ( this.props.domain.is_primary ? 'Yes' : 'No' ) }</td>
        <td>{ ( this.props.domain.active ? 'Yes' : 'No' ) }</td>
        <td>{ ( this.props.domain.is_https ? 'HTTPS' : 'HTTP' ) }</td>
      </tr>
    );
  }
}

export default DomainRow;
