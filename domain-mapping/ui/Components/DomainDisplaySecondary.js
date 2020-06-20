import { __, sprintf } from '@wordpress/i18n';
import React from 'react';

class DomainDisplaySecondary extends React.Component {
	/**
	 * Render component.
	 */
	render() {
		const url =
			( this.props.data.is_https ? 'https://' : 'http://' ) +
			this.props.data.domain;

		return (
			<td className="domain-options">
				<p>
					<a href={ url }>{ this.props.data.domain }</a>
				</p>
				{ this.props.data.is_active ? (
					<button onClick={ this.props.activate }>
						{ __( 'Deactivate', 'dark-matter' ) }
					</button>
				) : (
					<button onClick={ this.props.activate }>
						{ __( 'Activate', 'dark-matter' ) }
					</button>
				) }
				<span>|</span>
				<button onClick={ this.props.primary }>
					{ __( 'Set as Primary', 'dark-matter' ) }
				</button>
				<span>|</span>
				<button onClick={ this.props.protocol }>
					{ sprintf(
						/* translators: protocol */
						__( 'Change to %s', 'dark-matter' ),
						this.props.data.is_https
							? __( 'HTTP', 'dark-matter' )
							: __( 'HTTPS', 'dark-matter' )
					) }
				</button>
				<span>|</span>
				<button className="submitdelete" onClick={ this.props.delete }>
					{ __( 'Delete', 'dark-matter' ) }
				</button>
			</td>
		);
	}
}

export default DomainDisplaySecondary;
