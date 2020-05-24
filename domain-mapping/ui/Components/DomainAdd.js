import { __, sprintf } from '@wordpress/i18n';
import React from 'react';

import Domains from '../API/Domains';

class DomainAdd extends React.Component {
	/**
	 * Constructor.
	 *
	 * @param {Object} props
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
			},
		};
	}

	/**
	 * Helper method to make the AJAX call to add the domain to the database.
	 */
	async addDomain() {
		const result = await this.api.add( this.state.domain );

		let message = '';

		if ( result.code ) {
			message = sprintf(
				/* translators: error message */
				__( 'Cannot add domain. %s', 'dark-matter' ),
				result.message
			);
		} else {
			message = sprintf(
				/* translators: added domain */
				__( '%s; has been added.', 'dark-matter' ),
				result.domain
			);
		}

		this.props.addNoticeAndRefresh(
			this.state.domain.domain,
			message,
			result.code ? 'error' : 'success'
		);

		if ( ! result.code ) {
			this.reset();
		}
	}

	/**
	 * Handle the change event for each of the form elements.
	 *
	 * @param {Object} event Event Information.
	 */
	handleChange = ( event ) => {
		const name = event.target.name;
		const value = event.target.value;

		const domain = { ...this.state.domain };
		domain[ name ] = value;

		this.setState( {
			domain,
		} );
	};

	/**
	 * Handle the checkbox change for the protocol.
	 *
	 * @param {Object} event Event Information.
	 */
	handleCheckboxChange = ( event ) => {
		const name = event.target.name;

		const domain = { ...this.state.domain };
		domain[ name ] = event.target.checked;

		this.setState( {
			domain,
		} );
	};

	/**
	 * Handle the radio option change for the protocol.
	 *
	 * @param {Object} event Event Information.
	 */
	handleProtocol = ( event ) => {
		const value = event.target.value;

		const domain = { ...this.state.domain };
		domain.is_https = 'https' === value;

		this.setState( {
			domain,
		} );
	};

	/**
	 * Handle the form submission.
	 *
	 * @param {Object} event Event Information.
	 */
	handleSubmit = ( event ) => {
		event.preventDefault();

		this.addDomain();
	};

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
								<label htmlFor="domain">
									{ __( 'Domain', 'dark-matter' ) }
								</label>
							</th>
							<td>
								<input
									name="domain"
									type="text"
									onChange={ this.handleChange }
									value={ this.state.domain.domain }
								/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label htmlFor="is_primary">
									{ __( 'Is Primary?', 'dark-matter' ) }
								</label>
							</th>
							<td>
								<input
									name="is_primary"
									type="checkbox"
									value="yes"
									onChange={ this.handleCheckboxChange }
									checked={ this.state.domain.is_primary }
								/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label htmlFor="is_active">
									{ __( 'Is Active?', 'dark-matter' ) }
								</label>
							</th>
							<td>
								<input
									name="is_active"
									type="checkbox"
									value="yes"
									onChange={ this.handleCheckboxChange }
									checked={ this.state.domain.is_active }
								/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								{ __( 'Protocol', 'dark-matter' ) }
							</th>
							<td>
								<p>
									<input
										type="radio"
										name="is_https"
										id="protocol-http"
										value="http"
										onChange={ this.handleProtocol }
										checked={ ! this.state.domain.is_https }
									/>
									<label htmlFor="protocol-http">
										{ __( 'HTTP', 'dark-matter' ) }
									</label>
								</p>
								<p>
									<input
										type="radio"
										name="is_https"
										id="protocol-https"
										value="https"
										onChange={ this.handleProtocol }
										checked={ this.state.domain.is_https }
									/>
									<label htmlFor="protocol-https">
										{ __( 'HTTPS', 'dark-matter' ) }
									</label>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<p className="submit">
					<button type="submit" className="button button-primary">
						{ __( 'Add Domain', 'dark-matter' ) }
					</button>
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
			},
		} );
	}
}

export default DomainAdd;
