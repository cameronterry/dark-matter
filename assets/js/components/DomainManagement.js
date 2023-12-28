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

class DomainManagement extends Component {
	render() {
		return (
			<div className="dmp__domain-management">
				{ this.renderToolbar() }
				<Table />
			</div>
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
					variant="secondary"
				>
					{ __( 'Add New Domain', 'darkmatterplugin' ) }
				</Button>
			</div>
		);
	}
}

export default DomainManagement;
