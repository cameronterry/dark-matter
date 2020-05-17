import React from 'react';

import Domains from '../API/Domains';

class DomainAdd extends React.Component {
  /**
   * Constructor.
   *
   * @param {object} props
   */
  constructor( props ) {
    super( props );

    this.api = new Domains();

    this.state = {
      domain: {
        domain: '',
        is_primary: false,
        is_active: true,
        is_https: true,
      }
    };
  }

  /**
   * Helper method to make the AJAX call to add the domain to the database.
   */
  async addDomain() {
    let result = await this.api.add( this.state.domain );

    this.props.addNoticeAndRefresh( this.state.domain.domain, ( result.code ? 'Cannot add domain. ' + result.message : 'has been added.' ), ( result.code ? 'error' : 'success' ) );

    if ( ! result.code ) {
      this.reset();
    }
  }

  /**
   * Handle the change event for each of the form elements.
   *
   * @param {object} event Event Information.
   */
  handleChange = ( event ) => {
    const name = event.target.name;
    const value = event.target.value;

    let domain = { ...this.state.domain };
    domain[ name ] = value;

    this.setState( {
      domain : domain
    } );
  }

  /**
   * Handle the checkbox change for the protocol.
   *
   * @param {object} event Event Information.
   */
  handleCheckboxChange = ( event ) => {
    const name = event.target.name;

    let domain = { ...this.state.domain };
    domain[ name ] = event.target.checked;

    this.setState( {
      domain : domain
    } );
  }

  /**
   * Handle the radio option change for the protocol.
   *
   * @param {object} event Event Information.
   */
  handleProtocol = ( event ) => {
    const value = event.target.value;

    let domain = { ...this.state.domain };
    domain.is_https = ( 'https' === value );

    this.setState( {
      domain : domain
    } );
  }

  /**
   * Handle the form submission.
   *
   * @param {object} event Event Information.
   */
  handleSubmit = ( event ) => {
    event.preventDefault();

    this.addDomain();
  }

  /**
   * Render the component.
   */
  render() {
    return (
      <form onSubmit={ this.handleSubmit }>
        <h2>Add Domain</h2>
        <table className="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label htmlFor="domain">Domain</label>
              </th>
              <td>
                <input name="domain" type="text" onChange={ this.handleChange } value={ this.state.domain.domain } />
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label htmlFor="is_primary">Is Primary?</label>
              </th>
              <td>
                <input name="is_primary" type="checkbox" value="yes" onChange={ this.handleCheckboxChange } checked={ this.state.domain.is_primary } />
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label htmlFor="is_active">Is Active?</label>
              </th>
              <td>
                <input name="is_active" type="checkbox" value="yes" onChange={ this.handleCheckboxChange } checked={ this.state.domain.is_active } />
              </td>
            </tr>
            <tr>
              <th scope="row">
                Protocol
              </th>
              <td>
                <p>
                  <input type="radio" name="is_https" id="protocol-http" value="http" onChange={ this.handleProtocol } checked={ ! this.state.domain.is_https } />
                  <label htmlFor="protocol-http">HTTP</label>
                </p>
                <p>
                  <input type="radio" name="is_https" id="protocol-https" value="https" onChange={ this.handleProtocol } checked={ this.state.domain.is_https } />
                  <label htmlFor="protocol-https">HTTPS</label>
                </p>
              </td>
            </tr>
          </tbody>
        </table>
        <p className="submit">
          <button type="submit" className="button button-primary">Add Domain</button>
        </p>
      </form>
    );
  }

  /**
   * Reset the form back to the default.
   */
  reset() {
    this.setState( {
      domain: {
        domain: '',
        is_primary: false,
        is_active: true,
        is_https: false,
      }
    } );
  }
}

export default DomainAdd;
