import { __ } from '@wordpress/i18n';
import React from 'react';

class DomainDisplayMedia extends React.Component {
	/**
	 * Render component
	 */
	render() {
		const url = 'https://' + this.props.data.domain;

		return (
			<td className="domain-options">
				<p>
					<strong>
						<a href={ url }>{ this.props.data.domain }</a>
					</strong>
				</p>
				<button onClick={ false }>
					{ __( 'Convert to Secondary domain', 'dark-matter' ) }
				</button>
				<span>|</span>
				<button className="submitdelete" onClick={ this.props.delete }>
					{ __( 'Delete', 'dark-matter' ) }
				</button>
			</td>
		);
	}
}

export default DomainDisplayMedia;
