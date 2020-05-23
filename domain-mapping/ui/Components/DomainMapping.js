import React from 'react';

import DomainAdd from './DomainAdd';
import Domains from '../API/Domains';
import DomainRow from './DomainRow';
import Message from './Message';

class DomainMapping extends React.Component {
	/**
	 * Constructor.
	 *
	 * @param {Object} props
	 */
	constructor( props ) {
		super( props );

		this.api = new Domains();

		this.state = {
			domains: [],
			messages: [],
		};
	}

	/**
	 * Helper method to add a notice to Messages.
	 *
	 * @param {string} domain FQDN which the notice is applied to.
	 * @param {string} text   Message to be displayed in the notice.
	 * @param {string} type   Two types; "success" or "error".
	 */
	addNotice( domain, text, type ) {
		this.setState( {
			messages: [
				...this.state.messages,
				{
					id: new Date().getTime(),
					domain,
					text,
					type,
				},
			],
		} );
	}

	/**
	 * Helper method to add a notice to Messages and Refresh the table.
	 *
	 * @param {string} domain FQDN which the notice is applied to.
	 * @param {string} text   Message to be displayed in the notice.
	 * @param {string} type   Two types; "success" or "error".
	 */
	addNoticeAndRefresh = ( domain, text, type ) => {
		this.addNotice( domain, text, type );

		this.getData();
	};

	/**
	 * Retrieve the domains for the Site from the REST API.
	 */
	componentDidMount() {
		this.getData();
	}

	/**
	 * Performs the delete call to the REST API and handle the error message.
	 *
	 * @param {string} domain FQDN to be deleted.
	 */
	async delete( domain ) {
		const result = await this.api.delete( domain );

		this.addNotice(
			domain,
			result.code ? result.message : 'has been deleted.',
			result.code ? 'error' : 'success'
		);

		this.getData();
	}

	/**
	 * Handle the removal of the Notice from state.
	 *
	 * @param {string} id Element ID.
	 * @param {number} index Message index to be removed from the array.
	 */
	dimissNotice = ( id, index ) => {
		this.setState( {
			messages: [
				...this.state.messages.slice( 0, index ),
				...this.state.messages.slice( index + 1 ),
			],
		} );
	};

	/**
	 * Method for retrieve all the domains from the REST API.
	 */
	async getData() {
		const result = await this.api.getAll();

		this.setState( {
			domains: result,
		} );
	}

	/**
	 * Handle the Delete of a domain.
	 *
	 * @param {string} domain FQDN to be deleted.
	 */
	handleDelete = ( domain ) => {
		this.delete( domain );
	};

	/**
	 * Handle the update for a domain.
	 *
	 * @param {Object} data Data set containing the updates for a domain record.
	 */
	handleUpdate = ( data ) => {
		this.update( data );
	};

	/**
	 * Render the component.
	 */
	render() {
		const messages = [];
		const rows = [];

		this.state.domains.forEach( ( domain ) => {
			rows.push(
				<DomainRow
					key={ domain.id }
					delete={ this.handleDelete }
					domain={ domain }
					update={ this.handleUpdate }
				/>
			);
		} );

		this.state.messages.forEach( ( message, index ) => {
			messages.push(
				<Message
					key={ message.id }
					dismiss={ this.dimissNotice }
					index={ index }
					notice={ message }
				/>
			);
		} );

		return (
			<div className="wrap">
				<h1 className="wp-heading-inline">Domains</h1>
				<hr className="wp-header-end" />
				{ messages }
				<table className="wp-list-table widefat fixed striped users">
					<thead>
						<tr>
							<th scope="col" className="manage-column">
								Domain
							</th>
							<th scope="col" className="manage-column">
								Is Primary?
							</th>
							<th scope="col" className="manage-column">
								Active?
							</th>
							<th scope="col" className="manage-column">
								Protocol
							</th>
						</tr>
					</thead>
					<tbody>{ rows }</tbody>
				</table>
				<DomainAdd addNoticeAndRefresh={ this.addNoticeAndRefresh } />
			</div>
		);
	}

	/**
	 * Perform the update call to the REST API.
	 *
	 * @param {Object} data Data set containing updates to be sent to the REST API.
	 */
	async update( data ) {
		const result = await this.api.update( data );

		this.addNotice(
			data.domain,
			result.code ? result.message : 'Successfully updated',
			result.code ? 'error' : 'success'
		);

		this.getData();
	}
}

export default DomainMapping;
