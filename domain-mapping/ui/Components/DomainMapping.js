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
      domains : [],
      messages : []
    };
  }

  /**
   * Retrieve the domains for the Site from the REST API.
   */
  componentDidMount() {
    this.getData();
  }

  dimissNotice = ( id, index ) => {
    this.setState( {
      messages: [
        ...this.state.messages.slice( 0, index ),
        ...this.state.messages.slice( index + 1 )
      ]
    } );
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

    this.state.messages.forEach( ( message, index ) => {
      messages.push( <Message key={ message.id } dismiss={ this.dimissNotice } index={ index } notice={ message } /> );
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

  async update( data ) {
    let messages = [ ...this.state.messages ];
    const result = await this.api.update( data );

    if ( result.code ) {
      messages.push( {
        id: new Date().getTime(),
        domain: data.domain,
        text: result.message,
        type: 'error',
      } );
    } else {
      messages.push( {
        id: new Date().getTime(),
        domain: data.domain,
        text: 'Successfully updated',
        type: 'success'
      } );
    }

    this.setState( {
      messages: messages
    } );
  }
}

export default DomainMapping;
