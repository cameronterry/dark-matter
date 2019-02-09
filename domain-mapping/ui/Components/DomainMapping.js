import React from 'react';
import DomainRow from './DomainRow';

class DomainMapping extends React.Component {
  render() {
    const rows = [];

    this.props.domains.forEach( ( domain ) => {
      rows.push( <DomainRow key={domain.id} domain={domain} /> );
    } );

    return (
      <div className="wrap">
        <h1>Domains</h1>
        <table className="wp-list-table widefat fixed striped users">
          <thead>
            <tr>
              <th scope="col" className="manage-column">Domain</th>
              <th scope="col" className="manage-column">Is Primary?</th>
              <th scope="col" className="manage-column">Active?</th>
              <th scope="col" className="manage-column">Protocol</th>
            </tr>
          </thead>
          <tbody>
            {rows}
          </tbody>
        </table>
      </div>
    );
  }
}

export default DomainMapping;
