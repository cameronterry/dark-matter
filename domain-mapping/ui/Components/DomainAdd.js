import React from 'react';

class DomainAdd extends React.Component {
  render() {
    return (
      <form>
        <h2>Add Domain</h2>
        <table className="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label for="domain">Domain</label>
              </th>
              <td>
                <input name="domain" type="text" />
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="is_primary">Is Primary?</label>
              </th>
              <td>
                <input name="is_primary" type="checkbox" value="yes" />
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="active">Is Active?</label>
              </th>
              <td>
                <input name="active" type="checkbox" checked="checked" value="yes" />
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="active">Protocol</label>
              </th>
              <td>
                <p>
                  <input type="radio" name="is_https" id="protocol-http" value="allow" />
                  <label for="protocol-http">HTTP</label>
                </p>
                <p>
                  <input type="radio" name="is_https" id="protocol-https" value="allow" />
                  <label for="protocol-https">HTTPS</label>
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
}

export default DomainAdd;
