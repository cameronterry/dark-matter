/**
 * WordPress dependencies.
 */
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import Card from './Card';

class Grid extends Component {
	render() {
		return (
			<div className="dmp__domain-grid">
				{ this.renderDomains() }
			</div>
		);
	}

	renderDomains() {
		const { getDomains } = this.props;

		const domains = getDomains();

		return (
			<>
				{
					domains.length > 0 &&
					domains.map( ( item ) => {
						return <Card key={ item.id } { ...item } />;
					} )
				}
			</>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		return {
			getDomains: select( 'darkmatterplugin/domains' ).getDomains,
			pagination: select( 'darkmatterplugin/domains' ).getPagination(),
		};
	} ),
] )( Grid );
