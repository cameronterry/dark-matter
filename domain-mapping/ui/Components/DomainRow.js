import React from 'react';

class DomainRow extends React.Component {
  render() {
    return (
      <tr>
        <td>
          <p>
            <strong>{ this.props.domain.domain }</strong>
          </p>
          <a href="#">Set as Primary</a> | <a href="#">Change to HTTPS</a>
        </td>
        <td>{ (this.props.domain.is_primary ? 'Yes' : 'No' ) }</td>
        <td>{ (this.props.domain.active ? 'Yes' : 'No' ) }</td>
        <td>{ (this.props.domain.is_https ? 'HTTPS' : 'HTTP' ) }</td>
      </tr>
    );
  }
}

export default DomainRow;
