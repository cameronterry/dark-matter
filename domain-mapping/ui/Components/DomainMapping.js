import React from 'react';

import Domains from '../API/Domains';
import DomainRow from './DomainRow';
import Message from './Message';

class DomainMapping extends React.Component {
  /**
   * Constructor.
   *
   * @param {object} props
   */
  constructor( props ) {
    super( props );

    this.api = new Domains();

    this.state = {
      domains : []
    };
  }

  /**
   * Retrieve the domains for the Site from the REST API.
   */
  componentDidMount() {
    this.getData();
  }

  /**
   * Method for retrieve all the domains from the REST API.
   */
  async getData() {
    const result = await this.api.getAll();

    this.setState( {
      domains: result
    } );
  }

  /**
   * Render the component.
   */
  render() {
    const messages = [];
    const rows = [];

    this.state.domains.forEach( ( domain ) => {
      rows.push( <DomainRow key={ domain.id } delete={ this.delete } domain={ domain } update={ this.update } /> );
    } );

    this.state.messages.forEach( ( message ) => {
      messages.push( <Message message={ message.text } type={ message.type } /> );
    } );

    return (
      <div className="wrap">
        <h1 className="wp-heading-inline">Domains</h1>
        <a href="#" className="page-title-action">Add New</a>
        <hr className="wp-header-end" />
        { messages }
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
            { rows }
          </tbody>
        </table>
      </div>
    );
  }
}

export default DomainMapping;
