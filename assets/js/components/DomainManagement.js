/**
 * WordPress dependencies
 */
import { Button, SearchControl } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Table from './domain/Table';
import { NewDomainModal } from './domain/modals/NewDomain';

class DomainManagement extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			modalNewDomainOpen: false,
		};
	}

	render() {
		return (
			<div className="dmp__domain-management">
				{ this.renderToolbar() }
				<Table />
				{ this.renderModals() }
			</div>
		);
	}

	renderModals() {
		const { modalNewDomainOpen } = this.state;

		return (
			<>
				{ modalNewDomainOpen && (
					<NewDomainModal
						onClose={ () => {
							this.setState( { modalNewDomainOpen: false } );
						} }
					/>
				) }
			</>
		);
	}

	renderToolbar() {
		return (
			<div className="dmp__domain-management-toolbar">
				<SearchControl
					label={ __( 'Filter domains', 'darkmatterplugin' ) }
					size="compact"
				/>
				<Button
					onClick={ () => {
						this.setState( { modalNewDomainOpen: true } );
					} }
					variant="secondary"
				>
					{ __( 'Add New Domain', 'darkmatterplugin' ) }
				</Button>
			</div>
		);
	}
}

export default DomainManagement;
